<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Payment;
use App\Services\OrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPaymentWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        private string $gateway,
        private array $processedData,
    ) {
    }

    public function handle(OrderService $orderService): void
    {
        $paymentId = $this->processedData['payment_id'];
        $status = $this->processedData['status'];
        $metadata = $this->processedData['metadata'];

        // Check idempotency
        $existingPayment = Payment::where('gateway_payment_id', $paymentId)
            ->where('gateway', $this->gateway)
            ->first();

        if ($existingPayment && $existingPayment->gateway_status === $status) {
            Log::info("Webhook already processed", ['payment_id' => $paymentId, 'status' => $status]);
            return;
        }

        if ($existingPayment) {
            $existingPayment->update([
                'gateway_status' => $status,
                'metadata' => $metadata,
            ]);

            $order = $existingPayment->order;

            if ($status === 'paid') {
                $orderService->markAsPaid($order);
            }
        }

        Log::info("Webhook processed", [
            'gateway' => $this->gateway,
            'payment_id' => $paymentId,
            'status' => $status,
        ]);
    }
}
