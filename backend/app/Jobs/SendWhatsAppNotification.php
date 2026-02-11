<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        private Order $order,
    ) {
    }

    public function handle(WhatsAppService $whatsAppService): void
    {
        $store = $this->order->store;

        if (!$store->whatsapp) {
            Log::info("Store has no WhatsApp configured", ['store_id' => $store->id]);
            return;
        }

        $link = $whatsAppService->generateOrderLink($store, $this->order);

        // Log the WhatsApp link (in production, this could send via WhatsApp Business API)
        Log::info("WhatsApp notification generated", [
            'order_number' => $this->order->order_number,
            'store_id' => $store->id,
            'whatsapp_link' => $link,
        ]);
    }
}
