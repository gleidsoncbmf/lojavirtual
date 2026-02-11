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

        return implode("\n", $lines);
    }
}
