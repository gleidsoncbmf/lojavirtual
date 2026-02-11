<?php

namespace App\Contracts;

use App\DTOs\CheckoutDTO;
use App\Models\Order;
use App\Models\Store;

interface PaymentGatewayInterface
{
    /**
     * Create a payment intent/preference for the given order.
     *
     * @return array{payment_id: string, payment_url: string|null, status: string}
     */
    public function createPayment(Order $order, Store $store, CheckoutDTO $checkout): array;

    /**
     * Process and validate a webhook payload.
     *
     * @return array{payment_id: string, status: string, metadata: array}
     */
    public function processWebhook(array $payload, array $headers): array;

    /**
     * Get the gateway identifier name.
     */
    public function getName(): string;
}
