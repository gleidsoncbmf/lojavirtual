<?php

namespace App\Http\Controllers\Api;

use App\DTOs\CheckoutDTO;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(
        private CheckoutService $checkoutService,
        private CartService $cartService,
        private WhatsAppService $whatsAppService,
    ) {
    }

    public function process(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email',
            'customer_phone' => 'nullable|string|max:20',
            'shipping_address' => 'nullable|array',
            'shipping_address.street' => 'nullable|string',
            'shipping_address.number' => 'nullable|string',
            'shipping_address.complement' => 'nullable|string',
            'shipping_address.neighborhood' => 'nullable|string',
            'shipping_address.city' => 'nullable|string',
            'shipping_address.state' => 'nullable|string',
            'shipping_address.zip' => 'nullable|string',
            'payment_method' => 'required|string|in:stripe,mercadopago,whatsapp',
            'session_id' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $store = $request->get('store');
        $sessionId = $request->header('X-Cart-Session', $validated['session_id'] ?? null);
        $cart = $this->cartService->getOrCreateCart($store, $sessionId);

        $checkout = CheckoutDTO::fromRequest($validated);

        try {
            $order = $this->checkoutService->processCheckout($store, $cart, $checkout);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $response = [
            'message' => 'Pedido realizado com sucesso!',
            'data' => new OrderResource($order),
        ];

        // If payment via WhatsApp, include the redirect URL
        if ($validated['payment_method'] === 'whatsapp') {
            $response['whatsapp_url'] = $this->checkoutService->getWhatsAppCheckoutUrl($store, $order);
        }

        return response()->json($response, 201);
    }
}
