<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\DTOs\CheckoutDTO;
use App\Events\OrderCreated;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Store;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    public function __construct(
        private CartService $cartService,
        private OrderService $orderService,
        private WhatsAppService $whatsAppService,
    ) {
    }

    public function processCheckout(Store $store, Cart $cart, CheckoutDTO $checkout): Order
    {
        return DB::transaction(function () use ($store, $cart, $checkout) {
            $cart->load('items.product', 'items.variation');

            if ($cart->items->isEmpty()) {
                throw new \InvalidArgumentException('O carrinho está vazio.');
            }

            // Validate stock
            foreach ($cart->items as $item) {
                $product = $item->product;
                if ($product->stock < $item->quantity) {
                    throw new \InvalidArgumentException(
                        "Estoque insuficiente para {$product->name}. Disponível: {$product->stock}"
                    );
                }
            }

            // Create order
            $order = Order::create([
                'store_id' => $store->id,
                'order_number' => Order::generateOrderNumber(),
                'customer_name' => $checkout->customerName,
                'customer_email' => $checkout->customerEmail,
                'customer_phone' => $checkout->customerPhone,
                'shipping_address' => $checkout->shippingAddress,
                'subtotal' => $cart->subtotal,
                'shipping_cost' => 0,
                'total' => $cart->subtotal,
                'payment_method' => $checkout->paymentMethod,
                'payment_status' => 'pending',
                'delivery_status' => 'pending',
                'notes' => $checkout->notes,
                'status_history' => [
                    [
                        'type' => 'payment',
                        'status' => 'pending',
                        'timestamp' => now()->toISOString(),
                    ]
                ],
            ]);

            // Create order items and decrement stock
            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_variation_id' => $item->product_variation_id,
                    'product_name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total' => $item->unit_price * $item->quantity,
                ]);

                $item->product->decrementStock($item->quantity);
            }

            // Clear cart
            $this->cartService->clearCart($cart);

            // Dispatch event
            event(new OrderCreated($order));

            return $order->load('items');
        });
    }

    public function getWhatsAppCheckoutUrl(Store $store, Order $order): ?string
    {
        if (!$store->whatsapp) {
            return null;
        }

        return $this->whatsAppService->generateOrderLink($store, $order);
    }
}
