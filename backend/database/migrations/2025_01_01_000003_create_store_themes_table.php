<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('store_themes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('primary_color')->default('#6366f1');
            $table->string('secondary_color')->default('#8b5cf6');
            $table->string('button_color')->default('#6366f1');
            $table->string('text_color')->default('#111827');
            $table->string('background_color')->default('#ffffff');
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->json('custom_css')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_themes');
    }
};
