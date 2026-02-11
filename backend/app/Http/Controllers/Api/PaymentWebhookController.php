<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessPaymentWebhook;
use App\Services\Payment\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentWebhookController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
    ) {
    }

    public function stripe(Request $request): JsonResponse
    {
        return $this->handleWebhook('stripe', $request);
    }

    public function mercadopago(Request $request): JsonResponse
    {
        return $this->handleWebhook('mercadopago', $request);
    }

    private function handleWebhook(string $gatewayName, Request $request): JsonResponse
    {
        try {
            $gateway = $this->paymentService->getGateway($gatewayName);

            $processedData = $gateway->processWebhook(
                $request->all(),
                $request->headers->all()
            );

            // Dispatch to queue for async processing
            ProcessPaymentWebhook::dispatch($gatewayName, $processedData);

            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
