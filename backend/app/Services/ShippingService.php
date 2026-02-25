<?php

namespace App\Services;

use App\Models\ShippingOption;
use App\Models\Store;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShippingService
{
    public function __construct(
        private CorreiosService $correiosService,
    ) {
    }

    /**
     * Calculate all available shipping options for a given ZIP code.
     *
     * @param Store $store
     * @param string $destinationZip CEP do comprador
     * @param Collection $cartItems Itens do carrinho (loaded with product + variation)
     * @return array
     */
    public function calculateShipping(Store $store, string $destinationZip, Collection $cartItems): array
    {
        $options = [];

        // 1. Get fixed-rate shipping options matching the destination
        $fixedOptions = $this->getMatchingFixedOptions($store, $destinationZip);
        foreach ($fixedOptions as $option) {
            $options[] = [
                'type' => 'fixed',
                'id' => $option->id,
                'name' => $option->name,
                'price' => (float) $option->price,
                'delivery_days' => $option->delivery_days,
            ];
        }

        // 2. Calculate via Correios if store has an origin ZIP
        if ($store->shipping_zip) {
            $dimensions = $this->calculateCartDimensions($cartItems);

            $correiosOptions = $this->correiosService->calculate(
                $store->shipping_zip,
                $destinationZip,
                $dimensions['weight'] / 1000, // grams to kg
                $dimensions['length'],
                $dimensions['width'],
                $dimensions['height'],
            );

            foreach ($correiosOptions as $correiosOption) {
                $options[] = [
                    'type' => 'correios',
                    'service' => $correiosOption['service'],
                    'name' => $correiosOption['name'],
                    'price' => $correiosOption['price'],
                    'delivery_days' => $correiosOption['delivery_days'],
                ];
            }
        }

        return $options;
    }

    /**
     * Get fixed-rate options matching the destination ZIP.
     * We resolve the ZIP to city/state via ViaCEP and match.
     */
    private function getMatchingFixedOptions(Store $store, string $destinationZip): Collection
    {
        $activeOptions = $store->shippingOptions()->active()->get();

        if ($activeOptions->isEmpty()) {
            return collect();
        }

        // Resolve destination address via ViaCEP
        $address = $this->resolveZip($destinationZip);

        if (!$address) {
            // Can't resolve — return options without city/state filter (generic ones)
            return $activeOptions->filter(function ($option) {
                return empty($option->city) && empty($option->state);
            });
        }

        return $activeOptions->filter(function ($option) use ($address) {
            // Generic option (no city/state restriction)
            if (empty($option->city) && empty($option->state)) {
                return true;
            }

            // Match by state only
            if (empty($option->city) && !empty($option->state)) {
                return mb_strtoupper($option->state) === mb_strtoupper($address['state']);
            }

            // Match by city and state
            $cityMatch = mb_strtolower(trim($option->city)) === mb_strtolower(trim($address['city']));
            $stateMatch = empty($option->state) || mb_strtoupper($option->state) === mb_strtoupper($address['state']);

            return $cityMatch && $stateMatch;
        });
    }

    /**
     * Resolve a ZIP code to city/state via ViaCEP.
     */
    private function resolveZip(string $zip): ?array
    {
        $zip = preg_replace('/\D/', '', $zip);

        if (strlen($zip) !== 8) {
            return null;
        }

        try {
            $response = Http::timeout(5)->get("https://viacep.com.br/ws/{$zip}/json/");

            if (!$response->successful() || isset($response->json()['erro'])) {
                return null;
            }

            $data = $response->json();

            return [
                'city' => $data['localidade'] ?? '',
                'state' => $data['uf'] ?? '',
                'street' => $data['logradouro'] ?? '',
                'neighborhood' => $data['bairro'] ?? '',
            ];
        } catch (\Exception $e) {
            Log::warning('ViaCEP API error', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Calculate total cart dimensions for Correios.
     * Uses per-variation dimensions when available, falls back to product.
     */
    private function calculateCartDimensions(Collection $cartItems): array
    {
        $totalWeight = 0;
        $maxLength = 0;
        $maxWidth = 0;
        $totalHeight = 0;

        foreach ($cartItems as $item) {
            $variation = $item->variation;
            $product = $item->product;
            $qty = $item->quantity;

            // Use variation dimensions if set, otherwise product
            $weight = ($variation?->weight ?? $product->weight) ?: 300; // default 300g
            $length = ($variation?->length ?? $product->length) ?: 20;  // default 20cm
            $width = ($variation?->width ?? $product->width) ?: 15;    // default 15cm
            $height = ($variation?->height ?? $product->height) ?: 5;   // default 5cm

            $totalWeight += $weight * $qty;
            $maxLength = max($maxLength, $length);
            $maxWidth = max($maxWidth, $width);
            $totalHeight += $height * $qty;
        }

        // Cap height at 100cm (Correios max)
        $totalHeight = min($totalHeight, 100);

        return [
            'weight' => $totalWeight,
            'length' => $maxLength,
            'width' => $maxWidth,
            'height' => $totalHeight,
        ];
    }

    /**
     * Validate a selected shipping option — returns price and name.
     */
    public function resolveSelectedOption(
        Store $store,
        string $destinationZip,
        Collection $cartItems,
        ?int $shippingOptionId,
        ?string $shippingService,
    ): array {
        // Fixed-rate option
        if ($shippingOptionId) {
            $option = $store->shippingOptions()
                ->active()
                ->where('id', $shippingOptionId)
                ->first();

            if (!$option) {
                throw new \InvalidArgumentException('Opção de frete inválida.');
            }

            return [
                'cost' => (float) $option->price,
                'method' => $option->name,
            ];
        }

        // Correios service
        if ($shippingService && $store->shipping_zip) {
            $dimensions = $this->calculateCartDimensions($cartItems);

            $correiosOptions = $this->correiosService->calculate(
                $store->shipping_zip,
                $destinationZip,
                $dimensions['weight'] / 1000,
                $dimensions['length'],
                $dimensions['width'],
                $dimensions['height'],
            );

            foreach ($correiosOptions as $option) {
                if ($option['service'] === $shippingService) {
                    return [
                        'cost' => $option['price'],
                        'method' => $option['name'],
                    ];
                }
            }

            throw new \InvalidArgumentException('Serviço de frete dos Correios indisponível.');
        }

        throw new \InvalidArgumentException('Nenhuma opção de frete selecionada.');
    }

    // ---- CRUD helpers for admin ----

    public function getStoreOptions(Store $store): Collection
    {
        return $store->shippingOptions()->orderBy('name')->get();
    }

    public function createOption(Store $store, array $data): ShippingOption
    {
        return $store->shippingOptions()->create($data);
    }

    public function updateOption(ShippingOption $option, array $data): ShippingOption
    {
        $option->update($data);
        return $option->fresh();
    }

    public function deleteOption(ShippingOption $option): void
    {
        $option->delete();
    }
}
