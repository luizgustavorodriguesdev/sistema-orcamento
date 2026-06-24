<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Executa as modificações na tabela.
     */
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            // Adicionando os novos campos sem exigir uma ordem específica
            $table->string('customer_phone')->nullable();
            $table->string('cep', 9)->nullable();
            $table->string('address_street')->nullable();
            $table->string('address_neighborhood')->nullable();
            $table->string('address_city')->nullable();
            $table->string('address_state', 2)->nullable();
            $table->text('customization_details')->nullable();
        });
    }

    /**
     * Reverte as modificações (caso precise desfazer).
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn([
                'customer_phone',
                'cep',
                'address_street',
                'address_neighborhood',
                'address_city',
                'address_state',
                'customization_details'
            ]);
        });
    }
};