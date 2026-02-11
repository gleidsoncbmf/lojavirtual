<?php

namespace App\DTOs;

class OrderDTO
{
    public function __construct(
        public readonly int $storeId,
        public readonly string $orderNumber,
        public readonly string $customerName,
        public readonly ?string $customerEmail,
        public readonly ?string $customerPhone,
        public readonly ?array $shippingAddress,
        public readonly float $subtotal,
        public readonly float $shippingCost,
        public readonly float $total,
        public readonly string $paymentMethod,
        public readonly ?string $notes,
    ) {
    }

    public function toArray(): array
    {
        return [
            'store_id' => $this->storeId,
            'order_number' => $this->orderNumber,
            'customer_name' => $this->customerName,
            'customer_email' => $this->customerEmail,
            'customer_phone' => $this->customerPhone,
            'shipping_address' => $this->shippingAddress,
            'subtotal' => $this->subtotal,
            'shipping_cost' => $this->shippingCost,
            'total' => $this->total,
            'payment_method' => $this->paymentMethod,
            'notes' => $this->notes,
        ];
    }
}
