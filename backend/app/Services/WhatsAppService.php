<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Store;

class WhatsAppService
{
    /**
     * Generate a WhatsApp link (wa.me) with the order details.
     */
    public function generateOrderLink(Store $store, Order $order): ?string
    {
        if (!$store->whatsapp) {
            return null;
        }

        $phone = preg_replace('/[^0-9]/', '', $store->whatsapp);
        $message = $this->buildOrderMessage($store, $order);

        return "https://wa.me/{$phone}?text=" . urlencode($message);
    }

    /**
     * Build the order message for WhatsApp.
     */
    public function buildOrderMessage(Store $store, Order $order): string
    {
        $order->load('items');

        $lines = [];
        $lines[] = "🛒 *Novo Pedido - {$store->name}*";
        $lines[] = "";
        $lines[] = "📋 *Pedido:* {$order->order_number}";
        $lines[] = "👤 *Cliente:* {$order->customer_name}";

        if ($order->customer_email) {
            $lines[] = "📧 *Email:* {$order->customer_email}";
        }
        if ($order->customer_phone) {
            $lines[] = "📱 *Telefone:* {$order->customer_phone}";
        }

        // Shipping address
        $address = $order->shipping_address;
        if (is_array($address) && !empty(array_filter($address))) {
            $lines[] = "";
            $lines[] = "📍 *Endereço de entrega:*";

            $addressParts = [];
            if (!empty($address['street'])) {
                $streetLine = $address['street'];
                if (!empty($address['number'])) {
                    $streetLine .= ", {$address['number']}";
                }
                $addressParts[] = $streetLine;
            }
            if (!empty($address['complement'])) {
                $addressParts[] = $address['complement'];
            }
            if (!empty($address['neighborhood'])) {
                $addressParts[] = $address['neighborhood'];
            }
            $cityState = [];
            if (!empty($address['city'])) {
                $cityState[] = $address['city'];
            }
            if (!empty($address['state'])) {
                $cityState[] = $address['state'];
            }
            if (!empty($cityState)) {
                $addressParts[] = implode(' - ', $cityState);
            }
            if (!empty($address['zip'])) {
                $addressParts[] = "CEP: {$address['zip']}";
            }

            foreach ($addressParts as $part) {
                $lines[] = "  {$part}";
            }
        }

        $lines[] = "";
        $lines[] = "📦 *Produtos:*";

        foreach ($order->items as $item) {
            $lines[] = "  • {$item->product_name} x{$item->quantity} — R$ " . number_format($item->total, 2, ',', '.');
        }

        $lines[] = "";
        $lines[] = "💰 *Subtotal:* R$ " . number_format($order->subtotal, 2, ',', '.');

        if ($order->shipping_cost > 0) {
            $lines[] = "🚚 *Frete:* R$ " . number_format($order->shipping_cost, 2, ',', '.');
        }

        $lines[] = "💵 *Total:* R$ " . number_format($order->total, 2, ',', '.');
        $lines[] = "";
        $lines[] = "💳 *Forma de pagamento:* {$order->payment_method}";

        if ($order->notes) {
            $lines[] = "";
            $lines[] = "📝 *Observações:* {$order->notes}";
        }

        // Order tracking link
        $frontendUrl = env('FRONTEND_STORE_URL', 'http://localhost:3000');
        $lines[] = "";
        $lines[] = "🔗 *Acompanhar pedido:*";
        $lines[] = "{$frontendUrl}/{$store->slug}/pedido/{$order->order_number}";

        return implode("\n", $lines);
    }
}
