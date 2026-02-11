<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Store;

class CartService
{
    public function getOrCreateCart(Store $store, ?string $sessionId, ?int $userId = null): Cart
    {
        $query = Cart::where('store_id', $store->id);

        if ($userId) {
            $query->where('user_id', $userId);
        } elseif ($sessionId) {
            $query->where('session_id', $sessionId);
        }

        $cart = $query->first();

        if (!$cart) {
            $cart = Cart::create([
                'store_id' => $store->id,
                'session_id' => $sessionId,
                'user_id' => $userId,
            ]);
        }

        return $cart->load('items.product', 'items.variation');
    }

    public function addItem(Cart $cart, Product $product, int $quantity = 1, ?ProductVariation $variation = null): CartItem
    {
        $existingItem = $cart->items()
            ->where('product_id', $product->id)
            ->where('product_variation_id', $variation?->id)
            ->first();

        if ($existingItem) {
            $existingItem->update([
                'quantity' => $existingItem->quantity + $quantity,
            ]);
            return $existingItem->fresh('product', 'variation');
        }

        $price = $variation?->price ?? $product->price;

        return CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variation_id' => $variation?->id,
            'quantity' => $quantity,
            'unit_price' => $price,
        ]);
    }

    public function updateItemQuantity(CartItem $item, int $quantity): CartItem
    {
        $item->update(['quantity' => $quantity]);
        return $item->fresh('product', 'variation');
    }

    public function removeItem(CartItem $item): bool
    {
        return $item->delete();
    }

    public function clearCart(Cart $cart): void
    {
        $cart->items()->delete();
    }

    public function getCartSummary(Cart $cart): array
    {
        $cart->load('items.product', 'items.variation');

        $subtotal = 0;
        $mappedItems = $cart->items->map(function (CartItem $item) use (&$subtotal) {
            $itemTotal = (float) $item->unit_price * $item->quantity;
            $subtotal += $itemTotal;

            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'variation_id' => $item->product_variation_id,
                'quantity' => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'total' => $itemTotal,
                'product' => [
                    'id' => $item->product->id,
                    'name' => $item->product->name,
                    'slug' => $item->product->slug,
                    'description' => $item->product->description,
                    'price' => (float) $item->product->price,
                    'compare_price' => $item->product->compare_price ? (float) $item->product->compare_price : null,
                    'sku' => $item->product->sku,
                    'stock' => $item->product->stock,
                    'active' => $item->product->active,
                    'images' => $item->product->images ?? [],
                ],
                'variation' => $item->variation ? [
                    'id' => $item->variation->id,
                    'name' => $item->variation->name,
                    'price' => $item->variation->price ? (float) $item->variation->price : null,
                    'stock' => $item->variation->stock,
                    'sku' => $item->variation->sku,
                ] : null,
            ];
        });

        return [
            'cart_id' => $cart->id,
            'session_id' => $cart->session_id,
            'items' => $mappedItems,
            'items_count' => $cart->items->sum('quantity'),
            'subtotal' => $subtotal,
            'total' => $subtotal,
        ];
    }
}
