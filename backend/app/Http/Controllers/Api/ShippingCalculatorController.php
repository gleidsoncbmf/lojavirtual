<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Services\ShippingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShippingCalculatorController extends Controller
{
    public function __construct(
        private ShippingService $shippingService,
        private CartService $cartService,
    ) {
    }

    /**
     * Calculate shipping options for a given ZIP code.
     * Uses the current cart items to determine weight/dimensions.
     */
    public function calculate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'zip' => 'required|string|min:8|max:9',
        ]);

        $store = $request->get('store');
        $sessionId = $request->header('X-Cart-Session');
        $cart = $this->cartService->getOrCreateCart($store, $sessionId);
        $cart->load('items.product', 'items.variation');

        if ($cart->items->isEmpty()) {
            return response()->json([
                'message' => 'Carrinho vazio.',
                'data' => [],
            ]);
        }

        $options = $this->shippingService->calculateShipping(
            $store,
            $validated['zip'],
            $cart->items,
        );

        return response()->json([
            'data' => $options,
        ]);
    }
}
