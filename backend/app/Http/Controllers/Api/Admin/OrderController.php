<?php

namespace App\Http\Controllers\Api\Admin;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private OrderService $orderService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $filters = $request->only(['payment_status', 'delivery_status', 'search']);

        $orders = $this->orderRepository->findByStoreId(
            $user->store_id,
            $filters,
            $request->integer('per_page', 15)
        );

        return response()->json(
            OrderResource::collection($orders)->response()->getData(true)
        );
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $order = $this->orderRepository->findById($id);

        if (!$order || $order->store_id !== $request->user()->store_id) {
            return response()->json(['message' => 'Pedido não encontrado.'], 404);
        }

        return response()->json([
            'data' => new OrderResource($order),
        ]);
    }

    public function updatePaymentStatus(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'payment_status' => 'required|in:pending,awaiting_payment,paid,cancelled',
        ]);

        $order = $this->orderRepository->findById($id);

        if (!$order || $order->store_id !== $request->user()->store_id) {
            return response()->json(['message' => 'Pedido não encontrado.'], 404);
        }

        $order = $this->orderService->updatePaymentStatus($order, $validated['payment_status']);

        return response()->json([
            'message' => 'Status de pagamento atualizado.',
            'data' => new OrderResource($order),
        ]);
    }

    public function updateDeliveryStatus(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'delivery_status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $order = $this->orderRepository->findById($id);

        if (!$order || $order->store_id !== $request->user()->store_id) {
            return response()->json(['message' => 'Pedido não encontrado.'], 404);
        }

        $order = $this->orderService->updateDeliveryStatus($order, $validated['delivery_status']);

        return response()->json([
            'message' => 'Status de entrega atualizado.',
            'data' => new OrderResource($order),
        ]);
    }

    public function markAsPaid(Request $request, int $id): JsonResponse
    {
        $order = $this->orderRepository->findById($id);

        if (!$order || $order->store_id !== $request->user()->store_id) {
            return response()->json(['message' => 'Pedido não encontrado.'], 404);
        }

        $order = $this->orderService->markAsPaid($order);

        return response()->json([
            'message' => 'Pedido marcado como pago.',
            'data' => new OrderResource($order),
        ]);
    }
}
