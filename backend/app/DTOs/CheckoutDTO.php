<?php

namespace App\DTOs;

class CheckoutDTO
{
    public function __construct(
        public readonly string $customerName,
        public readonly ?string $customerEmail,
        public readonly ?string $customerPhone,
        public readonly ?array $shippingAddress,
        public readonly string $paymentMethod,
        public readonly ?string $notes,
    ) {
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            customerName: $data['customer_name'],
            customerEmail: $data['customer_email'] ?? null,
            customerPhone: $data['customer_phone'] ?? null,
            shippingAddress: $data['shipping_address'] ?? null,
            paymentMethod: $data['payment_method'],
            notes: $data['notes'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'customer_name' => $this->customerName,
            'customer_email' => $this->customerEmail,
            'customer_phone' => $this->customerPhone,
            'shipping_address' => $this->shippingAddress,
            'payment_method' => $this->paymentMethod,
            'notes' => $this->notes,
        ];
    }
}
