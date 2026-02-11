<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class LogOrderActivity implements ShouldQueue
{
    public function handle(OrderStatusChanged $event): void
    {
        Log::info('Order status changed', [
            'order_id' => $event->order->id,
            'order_number' => $event->order->order_number,
            'store_id' => $event->order->store_id,
            'type' => $event->type,
            'status' => $event->status,
        ]);
    }
}
