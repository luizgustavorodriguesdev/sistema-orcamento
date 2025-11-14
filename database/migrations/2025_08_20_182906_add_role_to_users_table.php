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
        // Usamos Schema::table() para modificar uma tabela existente.
        Schema::table('users', function (Blueprint $table) {
            // Adicionamos a nova coluna 'role'.
            // Ela será do tipo string e terá o valor padrão 'vendedor'.
            // O método after('name') posiciona a nova coluna logo após a coluna 'name' no banco de dados.
            $table->string('role')->after('name')->default('vendedor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // A lógica de reversão é simplesmente remover a coluna que adicionámos.
            $table->dropColumn('role');
        });
    }
};
