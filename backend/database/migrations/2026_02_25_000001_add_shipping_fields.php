<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Weight & dimensions on products
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('weight', 10, 2)->nullable()->after('images')->comment('Weight in grams');
            $table->decimal('length', 10, 2)->nullable()->after('weight')->comment('Length in cm');
            $table->decimal('width', 10, 2)->nullable()->after('length')->comment('Width in cm');
            $table->decimal('height', 10, 2)->nullable()->after('width')->comment('Height in cm');
        });

        // Weight & dimensions on product variations (overrides product-level)
        Schema::table('product_variations', function (Blueprint $table) {
            $table->decimal('weight', 10, 2)->nullable()->after('image')->comment('Weight in grams');
            $table->decimal('length', 10, 2)->nullable()->after('weight')->comment('Length in cm');
            $table->decimal('width', 10, 2)->nullable()->after('length')->comment('Width in cm');
            $table->decimal('height', 10, 2)->nullable()->after('width')->comment('Height in cm');
        });

        // Store origin ZIP code for shipping calculation
        Schema::table('stores', function (Blueprint $table) {
            $table->string('shipping_zip', 9)->nullable()->after('banner_position');
        });

        // Shipping method on orders
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_method')->nullable()->after('shipping_cost');
        });

        // Fixed-rate shipping options managed by store admin
        Schema::create('shipping_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('city')->nullable();
            $table->string('state', 2)->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('delivery_days')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index('store_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_options');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('shipping_method');
        });

        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('shipping_zip');
        });

        Schema::table('product_variations', function (Blueprint $table) {
            $table->dropColumn(['weight', 'length', 'width', 'height']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['weight', 'length', 'width', 'height']);
        });
    }
};
