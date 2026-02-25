<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CorreiosService
{
    private const SERVICES = [
        '04014' => 'SEDEX',
        '04510' => 'PAC',
    ];

    // Use the ASMX web service endpoint (still active for anonymous access)
    private const API_URL = 'https://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo';

    /**
     * Calculate shipping via Correios for multiple services.
     *
     * @param string $originZip CEP de origem (loja)
     * @param string $destinationZip CEP de destino (comprador)
     * @param float $weightKg Peso em kg
     * @param float $length Comprimento em cm
     * @param float $width Largura em cm
     * @param float $height Altura em cm
     * @return array<int, array{service: string, name: string, price: float, delivery_days: int}>
     */
    public function calculate(
        string $originZip,
        string $destinationZip,
        float $weightKg,
        float $length,
        float $width,
        float $height,
    ): array {
        $originZip = preg_replace('/\D/', '', $originZip);
        $destinationZip = preg_replace('/\D/', '', $destinationZip);

        // Enforce Correios minimum dimensions
        $length = max($length, 16);
        $width = max($width, 11);
        $height = max($height, 2);
        $weightKg = max($weightKg, 0.3);

        $serviceCodes = implode(',', array_keys(self::SERVICES));

        $params = [
            'nCdEmpresa' => '',
            'sDsSenha' => '',
            'nCdServico' => $serviceCodes,
            'sCepOrigem' => $originZip,
            'sCepDestino' => $destinationZip,
            'nVlPeso' => number_format($weightKg, 2, '.', ''),
            'nCdFormato' => 1, // Caixa/Pacote
            'nVlComprimento' => $length,
            'nVlAltura' => $height,
            'nVlLargura' => $width,
            'nVlDiametro' => 0,
            'sCdMaoPropria' => 'N',
            'nVlValorDeclarado' => 0,
            'sCdAvisoRecebimento' => 'N',
        ];

        Log::info('Correios API request', [
            'url' => self::API_URL,
            'origin' => $originZip,
            'destination' => $destinationZip,
            'weight_kg' => $weightKg,
            'dimensions' => "{$length}x{$width}x{$height}",
        ]);

        try {
            $response = Http::timeout(15)->get(self::API_URL, $params);

            if (!$response->successful()) {
                Log::warning('Correios API request failed', [
                    'status' => $response->status(),
                    'body' => substr($response->body(), 0, 500),
                ]);
                return [];
            }

            $results = $this->parseResponse($response->body());

            Log::info('Correios API response', [
                'options_count' => count($results),
                'options' => $results,
            ]);

            return $results;
        } catch (\Exception $e) {
            Log::warning('Correios API error', [
                'message' => $e->getMessage(),
                'origin' => $originZip,
                'destination' => $destinationZip,
            ]);
            return [];
        }
    }

    /**
     * Parse the XML response from Correios.
     */
    private function parseResponse(string $xml): array
    {
        $results = [];

        try {
            $parsed = simplexml_load_string($xml);

            if (!$parsed) {
                Log::warning('Correios XML parsing failed — invalid XML', [
                    'xml_snippet' => substr($xml, 0, 300),
                ]);
                return [];
            }

            // The response may have cServico directly or nested under Servicos
            $services = null;
            if (isset($parsed->cServico)) {
                $services = $parsed->cServico;
            } elseif (isset($parsed->Servicos->cServico)) {
                $services = $parsed->Servicos->cServico;
            }

            if (!$services) {
                Log::warning('Correios response has no services', [
                    'xml_snippet' => substr($xml, 0, 300),
                ]);
                return [];
            }

            foreach ($services as $service) {
                $code = (string) $service->Codigo;
                $error = (string) $service->Erro;
                $errorMsg = (string) ($service->MsgErro ?? '');

                // Skip services with errors (except "0" which means no error)
                if ($error !== '0' && $error !== '') {
                    Log::info("Correios service {$code} skipped", [
                        'error_code' => $error,
                        'error_msg' => $errorMsg,
                    ]);
                    continue;
                }

                $price = (string) $service->Valor;
                $price = (float) str_replace(',', '.', str_replace('.', '', $price));

                if ($price <= 0) {
                    continue;
                }

                $results[] = [
                    'service' => $code,
                    'name' => self::SERVICES[$code] ?? "Correios ({$code})",
                    'price' => $price,
                    'delivery_days' => (int) $service->PrazoEntrega,
                ];
            }
        } catch (\Exception $e) {
            Log::warning('Correios XML parsing error', [
                'message' => $e->getMessage(),
            ]);
        }

        return $results;
    }
}
