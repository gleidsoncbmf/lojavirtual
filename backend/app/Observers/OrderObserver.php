<?php

namespace App\Observers;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    public function created(Order $order): void
    {
        Log::info("Order created: {$order->order_number}", [
            'store_id' => $order->store_id,
            'total' => $order->total,
        ]);
    }

    public function updated(Order $order): void
    {
        if ($order->wasChanged('payment_status')) {
            Log::info("Order payment status changed: {$order->order_number}", [
                'from' => $order->getOriginal('payment_status'),
                'to' => $order->payment_status,
            ]);
        }

        if ($order->wasChanged('delivery_status')) {
            Log::info("Order delivery status changed: {$order->order_number}", [
                'from' => $order->getOriginal('delivery_status'),
                'to' => $order->delivery_status,
            ]);
        }
    }
}
