<?php

namespace App\Services;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Events\PaymentConfirmed;
use App\Events\OrderStatusChanged;
use App\Models\Order;

class OrderService
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
    ) {
    }

    public function updatePaymentStatus(Order $order, string $status): Order
    {
        $order = $this->orderRepository->update($order, [
            'payment_status' => $status,
        ]);

        $order->addStatusHistory($status, 'payment');

        if ($status === 'paid') {
            event(new PaymentConfirmed($order));
        }

        event(new OrderStatusChanged($order, 'payment', $status));

        return $order;
    }

    public function updateDeliveryStatus(Order $order, string $status): Order
    {
        $order = $this->orderRepository->update($order, [
            'delivery_status' => $status,
        ]);

        $order->addStatusHistory($status, 'delivery');
        event(new OrderStatusChanged($order, 'delivery', $status));

        return $order;
    }

    public function markAsPaid(Order $order): Order
    {
        return $this->updatePaymentStatus($order, 'paid');
    }

    public function cancelOrder(Order $order): Order
    {
        $order = $this->orderRepository->update($order, [
            'payment_status' => 'cancelled',
            'delivery_status' => 'cancelled',
        ]);

        $order->addStatusHistory('cancelled', 'payment');
        $order->addStatusHistory('cancelled', 'delivery');

        return $order;
    }

    public function getDashboardStats(int $storeId): array
    {
        $orders = Order::where('store_id', $storeId);

        return [
            'total_orders' => $orders->count(),
            'pending_orders' => (clone $orders)->where('payment_status', 'pending')->count(),
            'paid_orders' => (clone $orders)->where('payment_status', 'paid')->count(),
            'total_revenue' => (clone $orders)->where('payment_status', 'paid')->sum('total'),
            'recent_orders' => (clone $orders)->latest()->limit(5)->with('items')->get(),
        ];
    }
}
