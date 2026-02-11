<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
    ) {
    }

    public function status(Request $request, string $orderNumber): JsonResponse
    {
        $order = $this->orderRepository->findByOrderNumber($orderNumber);

        if (!$order) {
            return response()->json(['message' => 'Pedido não encontrado.'], 404);
        }

        return response()->json([
            'data' => [
                'order_number' => $order->order_number,
                'payment_status' => $order->payment_status,
                'delivery_status' => $order->delivery_status,
                'total' => (float) $order->total,
                'items' => $order->items->map(fn($item) => [
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'total' => (float) $item->total,
                ]),
                'status_history' => $order->status_history ?? [],
                'created_at' => $order->created_at?->toISOString(),
            ],
        ]);
    }
}
