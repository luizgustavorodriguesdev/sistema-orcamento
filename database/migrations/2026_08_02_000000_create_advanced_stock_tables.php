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
        // 1. Campos extras em products
        Schema::table('products', function (Blueprint $table) {
            $table->integer('minimum_stock')->default(0)->after('stock_quantity');
            $table->decimal('cost_price', 10, 2)->default(0.00)->after('price');
        });

        // 2. Tabela de depósitos (warehouses)
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Tabela pivot de estoque por depósito
        Schema::create('product_warehouse', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(0);
            $table->primary(['product_id', 'warehouse_id']);
            $table->timestamps();
        });

        // 4. Tabela de fornecedores (suppliers)
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('cnpj')->unique()->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('contact_name')->nullable();
            $table->timestamps();
        });

        // 5. Tabela de ordens de compra (purchase_orders)
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->string('po_number')->unique();
            $table->string('status')->default('Rascunho'); // Rascunho, Aprovado, Recebido, Cancelado
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->text('observations')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->dateTime('received_at')->nullable();
            $table->timestamps();
        });

        // 6. Tabela de itens da ordem de compra (purchase_order_items)
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('price', 10, 2);
            $table->integer('received_quantity')->default(0);
            $table->timestamps();
        });

        // 7. Tabela de sessões de inventário (inventory_sessions)
        Schema::create('inventory_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->string('status')->default('Em_Andamento'); // Em_Andamento, Finalizado, Cancelado
            $table->string('description');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
        });

        // 8. Tabela de itens de inventário (inventory_items)
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_session_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('expected_quantity');
            $table->integer('counted_quantity')->nullable();
            $table->integer('adjusted_quantity')->nullable();
            $table->timestamps();
        });

        // 9. Modificar tabela stock_movements para comportar depósitos, compras e inventários
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->nullable()->after('user_id')->constrained()->onDelete('set null');
            $table->foreignId('purchase_order_id')->nullable()->after('quote_id')->constrained()->onDelete('set null');
            $table->foreignId('inventory_session_id')->nullable()->after('purchase_order_id')->constrained()->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropColumn('warehouse_id');
            $table->dropForeign(['purchase_order_id']);
            $table->dropColumn('purchase_order_id');
            $table->dropForeign(['inventory_session_id']);
            $table->dropColumn('inventory_session_id');
        });

        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('inventory_sessions');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('product_warehouse');
        Schema::dropIfExists('warehouses');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['minimum_stock', 'cost_price']);
        });
    }
};
