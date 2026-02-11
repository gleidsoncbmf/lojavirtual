<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Jobs\SendWhatsAppNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyStoreViaWhatsApp implements ShouldQueue
{
    public function handle(OrderCreated $event): void
    {
        SendWhatsAppNotification::dispatch($event->order);
    }
}
