<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->string('correios_user')->nullable()->after('shipping_zip');
            $table->text('correios_password')->nullable()->after('correios_user');
            $table->string('correios_cartao_postagem')->nullable()->after('correios_password');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn([
                'correios_user',
                'correios_password',
                'correios_cartao_postagem',
            ]);
        });
    }
};
