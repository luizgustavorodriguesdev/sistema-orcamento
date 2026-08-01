# Estrutura do Banco de Dados - Controle de Estoque

Este documento detalha as modificações estruturais no banco de dados necessárias para suportar a funcionalidade de controle de estoque.

## 1. Alterações na Tabela Existente: `products`

Adição de novos campos para controle de saldo de estoque.

| Campo | Tipo | Nulo | Padrão | Descrição |
| :--- | :--- | :--- | :--- | :--- |
| `track_stock` | BOOLEAN | Não | `false` | Indica se o estoque deste produto deve ser controlado pelo sistema. |
| `stock_quantity` | INTEGER | Não | `0` | Saldo atual do estoque do produto. |

### Código da Migration (`add_stock_fields_to_products_table`):
```php
Schema::table('products', function (Blueprint $table) {
    $table->boolean('track_stock')->default(false)->after('price');
    $table->integer('stock_quantity')->default(0)->after('track_stock');
});
```

---

## 2. Nova Tabela: `stock_movements`

Esta tabela armazena todo o histórico de alterações no estoque para fins de auditoria e rastreabilidade.

### Estrutura Detalhada

| Campo | Tipo | Nulo | Padrão | Descrição |
| :--- | :--- | :--- | :--- | :--- |
| `id` | BIGINT (PK) | Não | - | Chave primária auto-incremental. |
| `product_id` | BIGINT (FK) | Não | - | ID do produto movimentado (relação com `products`). |
| `user_id` | BIGINT (FK) | Sim | `NULL` | ID do usuário que fez a movimentação (relação com `users`). |
| `quote_id` | BIGINT (FK) | Sim | `NULL` | ID do orçamento associado (relação com `quotes`, se aplicável). |
| `quantity` | INTEGER | Não | - | Quantidade movimentada (positivo para entrada, negativo para saída). |
| `type` | VARCHAR(50) | Não | - | Tipo da movimentação: `addition`, `subtraction`, `adjustment`, `quote_approved`, `quote_cancelled`. |
| `description` | VARCHAR(255) | Sim | `NULL` | Descrição textual da movimentação (ex: "Entrada manual de estoque", "Dedução do Orçamento #0004"). |
| `created_at` | TIMESTAMP | Sim | `NULL` | Data e hora da movimentação. |
| `updated_at` | TIMESTAMP | Sim | `NULL` | Data e hora da atualização. |

### Código da Migration (`create_stock_movements_table`):
```php
Schema::create('stock_movements', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
    $table->foreignId('quote_id')->nullable()->constrained()->onDelete('set null');
    $table->integer('quantity');
    $table->string('type'); // addition, subtraction, adjustment, quote_approved, quote_cancelled
    $table->string('description')->nullable();
    $table->timestamps();
});
```
