<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('compare_price', 10, 2)->nullable();
            $table->string('sku')->nullable();
            $table->integer('stock')->default(0);
            $table->boolean('active')->default(true);
            $table->json('images')->nullable();
            $table->timestamps();

            $table->unique(['store_id', 'slug']);
            $table->index(['store_id', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
