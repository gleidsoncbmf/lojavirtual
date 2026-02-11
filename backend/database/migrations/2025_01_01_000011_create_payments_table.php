<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('gateway');
            $table->string('gateway_payment_id')->nullable();
            $table->string('gateway_status')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('BRL');
            $table->json('metadata')->nullable();
            $table->string('idempotency_key')->unique()->nullable();
            $table->timestamps();

            $table->index(['store_id', 'gateway']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
