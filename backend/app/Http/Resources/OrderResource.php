<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'customer_phone' => $this->customer_phone,
            'shipping_address' => $this->shipping_address,
            'subtotal' => (float) $this->subtotal,
            'shipping_cost' => (float) $this->shipping_cost,
            'total' => (float) $this->total,
            'payment_status' => $this->payment_status,
            'delivery_status' => $this->delivery_status,
            'payment_method' => $this->payment_method,
            'status_history' => $this->status_history ?? [],
            'notes' => $this->notes,
            'items' => $this->whenLoaded(
                'items',
                fn() =>
                $this->items->map(fn($item) => [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'total' => (float) $item->total,
                ])
            ),
            'payment' => $this->whenLoaded('payment', fn() => $this->payment ? [
                'gateway' => $this->payment->gateway,
                'status' => $this->payment->gateway_status,
                'amount' => (float) $this->payment->amount,
            ] : null),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
