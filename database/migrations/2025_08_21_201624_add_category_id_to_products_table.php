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
            // Adiciona a nova coluna para a chave estrangeira da categoria.
            // O método constrained() assume que a tabela é 'categories' e a coluna é 'id'.
            // O onDelete('set null') garante que, se uma categoria for apagada,
            // os produtos associados a ela não serão apagados, apenas o campo category_id ficará nulo.
            $table->foreignId('category_id')->nullable()->after('id')->constrained()->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // A lógica de reversão remove a chave estrangeira e a coluna.
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }
};
