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
        // Vamos criar uma tabela de configurações no formato "chave-valor".
        // Isso nos dá flexibilidade máxima para adicionar novas configurações no futuro.
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            // A 'key' (chave) será o nome da configuração (ex: 'company_name', 'company_cnpj').
            $table->string('key')->unique();
            // O 'value' (valor) armazenará a informação daquela configuração.
            // Usamos 'text' para permitir valores longos, como endereços ou observações.
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
