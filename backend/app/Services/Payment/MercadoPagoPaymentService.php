<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\DTOs\CheckoutDTO;
use App\Models\Order;
use App\Models\Store;

class MercadoPagoPaymentService implements PaymentGatewayInterface
{
    public function createPayment(Order $order, Store $store, CheckoutDTO $checkout): array
    {
        $config = $store->payment_config['mercadopago'] ?? [];

        // In production, this would use the MercadoPago SDK:
        // MercadoPago\SDK::setAccessToken($config['access_token']);
        // $preference = new MercadoPago\Preference();

        return [
            'payment_id' => 'mp_' . uniqid(),
            'payment_url' => null, // MercadoPago checkout URL would go here
            'status' => 'pending',
        ];
    }

    public function processWebhook(array $payload, array $headers): array
    {
        // In production, validate with MercadoPago:
        // Verify x-signature header

        $status = $payload['action'] ?? '';

        return [
            'payment_id' => (string) ($payload['data']['id'] ?? ''),
            'status' => $this->mapMercadoPagoStatus($status),
            'metadata' => $payload,
        ];
    }

    public function getName(): string
    {
        return 'mercadopago';
    }

    private function mapMercadoPagoStatus(string $action): string
    {
        return match ($action) {
            'payment.created' => 'awaiting_payment',
            'payment.updated' => 'paid',
            default => 'pending',
        };
    }
}
