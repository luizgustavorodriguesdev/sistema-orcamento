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
            // Adiciona a coluna slug apos a coluna name
            // o slug sera usando para criar URL amigavel para o produto ex: products/nome-do-produto
            // deve ser unico e nao deve ser editavel
            $table->string('slug')->unique()->after('name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('slug'); // Remove a coluna slug
        });
    }
};
