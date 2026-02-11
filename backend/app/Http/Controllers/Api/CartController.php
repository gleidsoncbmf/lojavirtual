<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        private CartService $cartService,
    ) {
    }

    public function show(Request $request): JsonResponse
    {
        $store = $request->get('store');
        $sessionId = $request->header('X-Cart-Session', $request->input('session_id'));

        $cart = $this->cartService->getOrCreateCart($store, $sessionId);
        $summary = $this->cartService->getCartSummary($cart);

        return response()->json([
            'data' => $summary,
        ]);
    }

    public function addItem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'variation_id' => 'nullable|exists:product_variations,id',
            'quantity' => 'integer|min:1',
            'session_id' => 'nullable|string',
        ]);

        $store = $request->get('store');
        $sessionId = $request->header('X-Cart-Session', $validated['session_id'] ?? null);
        $product = Product::findOrFail($validated['product_id']);

        if ($product->store_id !== $store->id) {
            return response()->json(['message' => 'Produto não pertence a esta loja.'], 403);
        }

        if (!$product->active || !$product->hasStock()) {
            return response()->json(['message' => 'Produto indisponível.'], 422);
        }

        $variation = isset($validated['variation_id'])
            ? ProductVariation::findOrFail($validated['variation_id'])
            : null;

        $cart = $this->cartService->getOrCreateCart($store, $sessionId);
        $item = $this->cartService->addItem($cart, $product, $validated['quantity'] ?? 1, $variation);

        return response()->json([
            'message' => 'Item adicionado ao carrinho.',
            'data' => $this->cartService->getCartSummary($cart->fresh('items.product', 'items.variation')),
        ], 201);
    }

    public function removeItem(Request $request, int $itemId): JsonResponse
    {
        $store = $request->get('store');
        $sessionId = $request->header('X-Cart-Session', $request->input('session_id'));

        $cart = $this->cartService->getOrCreateCart($store, $sessionId);
        $item = $cart->items()->findOrFail($itemId);

        $this->cartService->removeItem($item);

        return response()->json([
            'message' => 'Item removido do carrinho.',
            'data' => $this->cartService->getCartSummary($cart->fresh('items.product', 'items.variation')),
        ]);
    }

    public function updateItemQuantity(Request $request, int $itemId): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $store = $request->get('store');
        $sessionId = $request->header('X-Cart-Session', $request->input('session_id'));

        $cart = $this->cartService->getOrCreateCart($store, $sessionId);
        $item = $cart->items()->findOrFail($itemId);

        $this->cartService->updateItemQuantity($item, $validated['quantity']);

        return response()->json([
            'message' => 'Quantidade atualizada.',
            'data' => $this->cartService->getCartSummary($cart->fresh('items.product', 'items.variation')),
        ]);
    }
}
