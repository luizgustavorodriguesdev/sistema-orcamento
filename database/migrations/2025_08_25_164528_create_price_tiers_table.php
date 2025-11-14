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
        Schema::create('price_tiers', function (Blueprint $table) {
            $table->id();
            // Chave estrangeira que liga a escala de preço ao produto.
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            // Quantidade mínima para esta escala de preço.
            $table->integer('min_quantity');
            // Quantidade máxima para esta escala de preço.
            // Será 'nullable' para a última escala (ex: 100 ou mais).
            $table->integer('max_quantity')->nullable();
            // O preço por unidade para esta escala.
            $table->decimal('price', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_tiers');
    }
};
