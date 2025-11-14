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
        Schema::table('quotes', function (Blueprint $table) {
            // Adiciona a nova chave estrangeira para o cliente
            $table->foreignId('client_id')->nullable()->after('id')->constrained('clients')->onDelete('cascade');

            // Remove as colunas antigas que já não são necessárias
            $table->dropColumn(['client_name', 'client_contact']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            // Lógica para reverter: adiciona as colunas antigas de volta
            $table->string('client_name')->after('id');
            $table->string('client_contact')->after('client_name');

            // Remove a chave estrangeira
            $table->dropForeign(['client_id']);
            $table->dropColumn('client_id');
        });
    }
};
