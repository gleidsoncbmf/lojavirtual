<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\DTOs\CheckoutDTO;
use App\Events\OrderCreated;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Store;
use App\Services\ShippingService;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    public function __construct(
        private CartService $cartService,
        private OrderService $orderService,
        private WhatsAppService $whatsAppService,
        private ShippingService $shippingService,
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
                $variation = $item->variation;

                // If the item has a variation with stock tracking, check variation stock
                if ($variation && $variation->stock > 0) {
                    if ($variation->stock < $item->quantity) {
                        throw new \InvalidArgumentException(
                            "Estoque insuficiente para {$product->name} ({$variation->name}). Disponível: {$variation->stock}"
                        );
                    }
                } else {
                    // Fallback to product stock
                    if ($product->stock < $item->quantity) {
                        throw new \InvalidArgumentException(
                            "Estoque insuficiente para {$product->name}. Disponível: {$product->stock}"
                        );
                    }
                }
            }

            // Resolve shipping cost
            $shippingCost = 0;
            $shippingMethod = null;

            if ($checkout->shippingOptionId || $checkout->shippingService) {
                $destinationZip = $checkout->shippingAddress['zip'] ?? null;

                if ($destinationZip) {
                    $shipping = $this->shippingService->resolveSelectedOption(
                        $store,
                        $destinationZip,
                        $cart->items,
                        $checkout->shippingOptionId,
                        $checkout->shippingService,
                    );
                    $shippingCost = $shipping['cost'];
                    $shippingMethod = $shipping['method'];
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
                'shipping_cost' => $shippingCost,
                'shipping_method' => $shippingMethod,
                'total' => $cart->subtotal + $shippingCost,
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

                // Decrement variation stock if applicable, otherwise product stock
                if ($item->variation && $item->variation->stock > 0) {
                    $item->variation->decrement('stock', $item->quantity);
                } else {
                    $item->product->decrementStock($item->quantity);
                }
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
