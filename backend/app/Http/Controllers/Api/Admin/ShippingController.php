<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingOption;
use App\Services\ShippingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    public function __construct(
        private ShippingService $shippingService,
    ) {
    }

    /**
     * List all shipping options for the store.
     */
    public function index(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        return response()->json([
            'data' => $this->shippingService->getStoreOptions($store),
            'shipping_zip' => $store->shipping_zip,
            'has_correios_credentials' => !empty($store->correios_user) && !empty($store->correios_cartao_postagem),
            'correios_user' => $store->correios_user,
            'correios_cartao_postagem' => $store->correios_cartao_postagem,
        ]);
    }

    /**
     * Create a new fixed-rate shipping option.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:2',
            'price' => 'required|numeric|min:0',
            'delivery_days' => 'nullable|integer|min:0',
            'active' => 'boolean',
        ]);

        $store = $request->user()->store;
        $option = $this->shippingService->createOption($store, $validated);

        return response()->json([
            'message' => 'Opção de frete criada com sucesso.',
            'data' => $option,
        ], 201);
    }

    /**
     * Update a shipping option.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $store = $request->user()->store;
        $option = ShippingOption::where('store_id', $store->id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:2',
            'price' => 'sometimes|numeric|min:0',
            'delivery_days' => 'nullable|integer|min:0',
            'active' => 'sometimes|boolean',
        ]);

        $option = $this->shippingService->updateOption($option, $validated);

        return response()->json([
            'message' => 'Opção de frete atualizada.',
            'data' => $option,
        ]);
    }

    /**
     * Delete a shipping option.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $store = $request->user()->store;
        $option = ShippingOption::where('store_id', $store->id)->findOrFail($id);

        $this->shippingService->deleteOption($option);

        return response()->json([
            'message' => 'Opção de frete excluída.',
        ]);
    }

    /**
     * Update the store's origin ZIP code for shipping.
     */
    public function updateStoreZip(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'shipping_zip' => 'required|string|max:9',
        ]);

        $store = $request->user()->store;
        $store->update($validated);

        return response()->json([
            'message' => 'CEP de origem atualizado.',
            'shipping_zip' => $store->shipping_zip,
        ]);
    }

    /**
     * Update the store's Correios API credentials.
     */
    public function updateCorreiosCredentials(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'correios_user' => 'nullable|string|max:255',
            'correios_password' => 'nullable|string|max:255',
            'correios_cartao_postagem' => 'nullable|string|max:50',
        ]);

        $store = $request->user()->store;

        // Only update password if provided (allow clearing with empty string)
        $updateData = [
            'correios_user' => $validated['correios_user'] ?? null,
            'correios_cartao_postagem' => $validated['correios_cartao_postagem'] ?? null,
        ];

        // Only update password if it was sent (non-null)
        if (array_key_exists('correios_password', $validated) && $validated['correios_password'] !== null) {
            $updateData['correios_password'] = $validated['correios_password'];
        }

        $store->update($updateData);

        return response()->json([
            'message' => 'Credenciais dos Correios atualizadas.',
            'has_correios_credentials' => !empty($store->correios_user) && !empty($store->correios_cartao_postagem),
        ]);
    }
}
