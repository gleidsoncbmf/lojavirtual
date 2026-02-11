<?php

namespace App\Http\Controllers\Api;

use App\DTOs\StoreConfigDTO;
use App\Http\Controllers\Controller;
use App\Http\Resources\StoreResource;
use App\Models\Store;
use App\Services\Payment\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function config(Request $request): JsonResponse
    {
        $store = $request->get('store');
        $config = StoreConfigDTO::fromStore($store);

        return response()->json([
            'data' => $config->toArray(),
        ]);
    }

    public function paymentMethods(Request $request, PaymentService $paymentService): JsonResponse
    {
        $store = $request->get('store');
        $methods = $paymentService->getAvailableGateways($store);

        return response()->json([
            'data' => $methods,
        ]);
    }
}
