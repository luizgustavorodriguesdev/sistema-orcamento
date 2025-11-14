<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Adiciona a nova coluna para o preço promocional.
            // O método after('price') posiciona a nova coluna logo após a coluna 'price'.
            $table->decimal('promotional_price', 10, 2)->nullable()->after('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // A lógica de reversão remove a coluna que adicionámos.
            $table->dropColumn('promotional_price');
        });
    }
};
