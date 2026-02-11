<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\DTOs\CheckoutDTO;
use App\Models\Order;
use App\Models\Store;

class StripePaymentService implements PaymentGatewayInterface
{
    public function createPayment(Order $order, Store $store, CheckoutDTO $checkout): array
    {
        $config = $store->payment_config['stripe'] ?? [];

        // In production, this would use the Stripe SDK:
        // \Stripe\Stripe::setApiKey($config['secret_key']);
        // $session = \Stripe\Checkout\Session::create([...]);

        return [
            'payment_id' => 'stripe_' . uniqid(),
            'payment_url' => null, // Stripe Checkout URL would go here
            'status' => 'pending',
        ];
    }

    public function processWebhook(array $payload, array $headers): array
    {
        // In production, validate with Stripe signature:
        // $sig = $headers['stripe-signature'];
        // $event = \Stripe\Webhook::constructEvent($payload, $sig, $endpointSecret);

        return [
            'payment_id' => $payload['data']['object']['id'] ?? '',
            'status' => $this->mapStripeStatus($payload['type'] ?? ''),
            'metadata' => $payload['data']['object'] ?? [],
        ];
    }

    public function getName(): string
    {
        return 'stripe';
    }

    private function mapStripeStatus(string $eventType): string
    {
        return match ($eventType) {
            'payment_intent.succeeded' => 'paid',
            'payment_intent.payment_failed' => 'failed',
            'payment_intent.canceled' => 'cancelled',
            default => 'pending',
        };
    }
}
