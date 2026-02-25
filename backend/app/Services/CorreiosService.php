<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CorreiosService
{
    /**
     * Product code → human-readable name mapping.
     */
    private array $productNames;

    /**
     * Product codes for SEDEX and PAC.
     */
    private string $sedexCode;
    private string $pacCode;

    // ─── Fallback estimation constants (based on Correios 2025 pricing) ───
    private const FALLBACK = [
        'sedex' => [
            'name' => 'SEDEX',
            'base' => 25.00,
            'per_region' => 8.50,
            'weight_extra' => 4.50,
            'base_days' => 1,
            'days_per_region' => 1,
        ],
        'pac' => [
            'name' => 'PAC',
            'base' => 18.00,
            'per_region' => 5.00,
            'weight_extra' => 3.00,
            'base_days' => 5,
            'days_per_region' => 2,
        ],
    ];

    /**
     * Distance matrix between CEP regions (first digit of CEP → region index).
     */
    private const DISTANCE = [
        //0  1  2  3  4  5  6  7  8  9
        [0, 0, 1, 1, 2, 3, 4, 2, 2, 3], // 0 SP capital
        [0, 0, 1, 1, 2, 3, 4, 2, 2, 3], // 1 SP interior
        [1, 1, 0, 1, 2, 3, 4, 2, 3, 3], // 2 RJ/ES
        [1, 1, 1, 0, 2, 3, 3, 1, 2, 3], // 3 MG
        [2, 2, 2, 2, 0, 1, 2, 2, 3, 4], // 4 BA/SE
        [3, 3, 3, 3, 1, 0, 1, 3, 4, 5], // 5 PE/AL/PB/RN
        [4, 4, 4, 3, 2, 1, 0, 3, 4, 5], // 6 CE/PI/MA/PA/AM
        [2, 2, 2, 1, 2, 3, 3, 0, 2, 3], // 7 DF/GO/TO/MT/MS
        [2, 2, 3, 2, 3, 4, 4, 2, 0, 1], // 8 PR/SC
        [3, 3, 3, 3, 4, 5, 5, 3, 1, 0], // 9 RS
    ];

    public function __construct()
    {
        $this->sedexCode = config('correios.products.sedex', '03220');
        $this->pacCode = config('correios.products.pac', '03298');
        $this->productNames = config('correios.product_names', [
            '03220' => 'SEDEX',
            '03298' => 'PAC',
        ]);
    }

    /**
     * Calculate shipping costs for PAC and SEDEX.
     *
     * Strategy:
     * 1. If Correios credentials provided → try official REST API
     * 2. Fallback → built-in estimation based on CEP distance + weight
     */
    public function calculate(
        string $originZip,
        string $destinationZip,
        float $weightKg,
        float $length,
        float $width,
        float $height,
        ?string $correiosUser = null,
        ?string $correiosPassword = null,
        ?string $cartaoPostagem = null,
    ): array {
        $originZip = preg_replace('/\D/', '', $originZip);
        $destinationZip = preg_replace('/\D/', '', $destinationZip);

        // Enforce Correios minimum dimensions
        $length = max($length, 16);
        $width = max($width, 11);
        $height = max($height, 2);
        $weightKg = max($weightKg, 0.3);

        // 1. Try official REST API if credentials available
        if ($correiosUser && $correiosPassword && $cartaoPostagem) {
            $results = $this->callRestApi(
                $originZip,
                $destinationZip,
                $weightKg,
                $length,
                $width,
                $height,
                $correiosUser,
                $correiosPassword,
                $cartaoPostagem,
            );

            if (!empty($results)) {
                return $results;
            }
        }

        // 2. Fallback: built-in estimation
        Log::info('CorreiosService: using fallback estimation', [
            'reason' => ($correiosUser ? 'API call failed' : 'no credentials configured'),
        ]);

        return $this->estimateFallback($originZip, $destinationZip, $weightKg, $length, $width, $height);
    }

    // ────────────────────────────────────────────────────────────────────────
    // Official Correios REST API
    // ────────────────────────────────────────────────────────────────────────

    /**
     * Authenticate with Correios API and get a Bearer token.
     * Token is cached for 50 minutes (they expire in ~1 hour).
     */
    private function getToken(string $user, string $password, string $cartaoPostagem): ?string
    {
        $cacheKey = "correios_token_{$cartaoPostagem}";

        return Cache::remember($cacheKey, now()->addMinutes(config('correios.token_ttl', 50)), function () use ($user, $password, $cartaoPostagem) {
            $baseUrl = config('correios.base_url', 'https://api.correios.com.br');
            $endpoint = config('correios.token_endpoint', '/token/v1/autentica/cartaopostagem');

            Log::info('CorreiosService: authenticating with REST API', [
                'user' => $user,
                'cartao' => substr($cartaoPostagem, 0, 4) . '****',
            ]);

            try {
                $response = Http::timeout(config('correios.timeout', 10))
                    ->withBasicAuth($user, $password)
                    ->withHeaders([
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                    ])
                    ->post($baseUrl . $endpoint, [
                        'numero' => $cartaoPostagem,
                    ]);

                if (!$response->successful()) {
                    Log::warning('CorreiosService: auth failed', [
                        'status' => $response->status(),
                        'body' => substr($response->body(), 0, 300),
                    ]);
                    return null;
                }

                $data = $response->json();
                $token = $data['token'] ?? null;

                if ($token) {
                    Log::info('CorreiosService: auth successful');
                }

                return $token;
            } catch (\Exception $e) {
                Log::warning('CorreiosService: auth error', ['message' => $e->getMessage()]);
                return null;
            }
        });
    }

    /**
     * Call the Correios REST API for price and deadline.
     */
    private function callRestApi(
        string $originZip,
        string $destinationZip,
        float $weightKg,
        float $length,
        float $width,
        float $height,
        string $user,
        string $password,
        string $cartaoPostagem,
    ): array {
        $token = $this->getToken($user, $password, $cartaoPostagem);

        if (!$token) {
            // Invalidate cached null token
            Cache::forget("correios_token_{$cartaoPostagem}");
            return [];
        }

        $baseUrl = config('correios.base_url', 'https://api.correios.com.br');
        $timeout = config('correios.timeout', 10);
        $productCodes = [$this->sedexCode, $this->pacCode];

        // Determine object type: 2 = Caixa/Pacote
        $tpObjeto = 2;

        $results = [];

        // Calculate price for each service
        foreach ($productCodes as $coProduto) {
            try {
                $priceResult = $this->fetchPrice(
                    $baseUrl,
                    $token,
                    $timeout,
                    $coProduto,
                    $originZip,
                    $destinationZip,
                    $weightKg,
                    $length,
                    $width,
                    $height,
                    $tpObjeto,
                );

                $deadlineResult = $this->fetchDeadline(
                    $baseUrl,
                    $token,
                    $timeout,
                    $coProduto,
                    $originZip,
                    $destinationZip,
                );

                if ($priceResult !== null) {
                    $results[] = [
                        'service' => $coProduto,
                        'name' => $this->productNames[$coProduto] ?? "Correios ({$coProduto})",
                        'price' => $priceResult,
                        'delivery_days' => $deadlineResult,
                    ];
                }
            } catch (\Exception $e) {
                Log::warning("CorreiosService: REST API error for {$coProduto}", [
                    'message' => $e->getMessage(),
                ]);
            }
        }

        if (!empty($results)) {
            Log::info('CorreiosService: REST API success', [
                'count' => count($results),
                'options' => $results,
            ]);
        }

        return $results;
    }

    /**
     * Fetch price from the Correios REST API.
     */
    private function fetchPrice(
        string $baseUrl,
        string $token,
        int $timeout,
        string $coProduto,
        string $originZip,
        string $destinationZip,
        float $weightKg,
        float $length,
        float $width,
        float $height,
        int $tpObjeto,
    ): ?float {
        $endpoint = config('correios.price_endpoint', '/preco/v1/nacional');

        // Weight in grams for the API
        $weightGrams = (int) round($weightKg * 1000);

        $response = Http::timeout($timeout)
            ->withToken($token)
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->get($baseUrl . $endpoint, [
                'coProduto' => $coProduto,
                'cepOrigem' => $originZip,
                'cepDestino' => $destinationZip,
                'psObjeto' => $weightGrams,
                'tpObjeto' => $tpObjeto,
                'comprimento' => (int) ceil($length),
                'largura' => (int) ceil($width),
                'altura' => (int) ceil($height),
            ]);

        if (!$response->successful()) {
            Log::warning("CorreiosService: price API failed for {$coProduto}", [
                'status' => $response->status(),
                'body' => substr($response->body(), 0, 300),
            ]);
            return null;
        }

        $data = $response->json();

        // The response may vary: pcFinal or vlTotalPreco
        $price = $data['pcFinal'] ?? $data['vlTotalPreco'] ?? $data['pcBase'] ?? null;

        if ($price === null || (float) $price <= 0) {
            Log::warning("CorreiosService: no valid price for {$coProduto}", [
                'response' => $data,
            ]);
            return null;
        }

        return round((float) $price, 2);
    }

    /**
     * Fetch delivery deadline from the Correios REST API.
     */
    private function fetchDeadline(
        string $baseUrl,
        string $token,
        int $timeout,
        string $coProduto,
        string $originZip,
        string $destinationZip,
    ): ?int {
        $endpoint = config('correios.deadline_endpoint', '/prazo/v1/nacional');

        try {
            $response = Http::timeout($timeout)
                ->withToken($token)
                ->withHeaders([
                    'Accept' => 'application/json',
                ])
                ->get($baseUrl . $endpoint, [
                    'coProduto' => $coProduto,
                    'cepOrigem' => $originZip,
                    'cepDestino' => $destinationZip,
                ]);

            if (!$response->successful()) {
                Log::info("CorreiosService: deadline API failed for {$coProduto}", [
                    'status' => $response->status(),
                ]);
                return null;
            }

            $data = $response->json();

            return (int) ($data['prazoEntrega'] ?? $data['prazo'] ?? 0) ?: null;
        } catch (\Exception $e) {
            Log::info("CorreiosService: deadline error for {$coProduto}", [
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    // Fallback: built-in estimation when API is unavailable
    // ────────────────────────────────────────────────────────────────────────

    private function estimateFallback(
        string $originZip,
        string $destinationZip,
        float $weightKg,
        float $length,
        float $width,
        float $height,
    ): array {
        $originRegion = (int) ($originZip[0] ?? 0);
        $destRegion = (int) ($destinationZip[0] ?? 0);
        $distance = self::DISTANCE[$originRegion][$destRegion] ?? 3;

        // Volumetric weight (Correios formula: L×W×H / 6000)
        $cubicWeight = ($length * $width * $height) / 6000;
        $effectiveWeight = max($weightKg, $cubicWeight);
        $extraKg = max(0, $effectiveWeight - 1);

        $results = [];

        foreach (self::FALLBACK as $key => $svc) {
            $code = $key === 'sedex' ? $this->sedexCode : $this->pacCode;
            $price = round($svc['base'] + ($distance * $svc['per_region']) + ($extraKg * $svc['weight_extra']), 2);
            $days = $svc['base_days'] + ($distance * $svc['days_per_region']);

            $results[] = [
                'service' => $code,
                'name' => $svc['name'] . ' (estimativa)',
                'price' => $price,
                'delivery_days' => $days,
            ];
        }

        Log::info('CorreiosService: fallback estimation', [
            'count' => count($results),
            'options' => $results,
        ]);

        return $results;
    }
}
