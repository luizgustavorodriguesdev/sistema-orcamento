# Modelagem do Banco de Dados - Controle de Estoque Avançado

Este documento detalha o esquema físico de banco de dados, chaves estrangeiras, índices e alterações de tabelas planejadas para o módulo de estoque avançado.

---

## 1. Alterações em Tabelas Existentes

### Tabela: `products`
Adição de colunas para monitoramento financeiro e de compras:
- `minimum_stock` (INT, default 0, não nulo): Limite de segurança para reabastecimento.
- `cost_price` (DECIMAL(10,2), default 0.00, não nulo): Custo unitário pago ao fornecedor.

### Tabela: `stock_movements`
Expansão do log de auditoria para associar movimentações a compras, inventários e depósitos:
- `warehouse_id` (BIGINT, FK para `warehouses`, não nulo): O depósito afetado.
- `purchase_order_id` (BIGINT, FK para `purchase_orders`, nulo, opcional): Se gerado por check-in de recebimento.
- `inventory_session_id` (BIGINT, FK para `inventory_sessions`, nulo, opcional): Se gerado por inventário corretivo.

---

## 2. Novas Tabelas

### Tabela: `warehouses` (Depósitos)
Armazena os locais físicos de armazenagem de estoque.
- `id` (BIGINT, chave primária, auto-incremento)
- `name` (VARCHAR(255), não nulo): Nome descritivo (ex: "Depósito Central").
- `code` (VARCHAR(50), único, não nulo): Código curto (ex: "DEP-CENT").
- `description` (TEXT, nulo)
- `is_active` (TINYINT(1), default 1, não nulo)
- `timestamps`

### Tabela: `product_warehouse` (Tabela Pivot de Saldos)
Mapeia a quantidade específica de cada produto por depósito.
- `product_id` (BIGINT, FK para `products`, cascata na exclusão)
- `warehouse_id` (BIGINT, FK para `warehouses`, cascata na exclusão)
- `quantity` (INT, default 0, não nulo)
- **Chave Primária**: Composta por `(product_id, warehouse_id)`

### Tabela: `suppliers` (Fornecedores)
Armazena a base de dados de fornecedores parceiros.
- `id` (BIGINT, chave primária, auto-incremento)
- `name` (VARCHAR(255), não nulo)
- `cnpj` (VARCHAR(20), único, nulo)
- `email` (VARCHAR(255), nulo)
- `phone` (VARCHAR(50), nulo)
- `contact_name` (VARCHAR(255), nulo)
- `timestamps`

### Tabela: `purchase_orders` (Ordens de Compra)
Registra pedidos de compras e entradas fiscais/físicas.
- `id` (BIGINT, chave primária, auto-incremento)
- `supplier_id` (BIGINT, FK para `suppliers`)
- `po_number` (VARCHAR(50), único, não nulo): Código de rastreamento da compra.
- `status` (ENUM('Rascunho', 'Aprovado', 'Recebido', 'Cancelado'), default 'Rascunho')
- `total_amount` (DECIMAL(10,2), default 0.00)
- `observations` (TEXT, nulo)
- `created_by` (BIGINT, FK para `users`)
- `received_at` (DATETIME, nulo)
- `timestamps`

### Tabela: `purchase_order_items` (Itens de Compras)
Itens solicitados em cada ordem de compra.
- `id` (BIGINT, chave primária, auto-incremento)
- `purchase_order_id` (BIGINT, FK para `purchase_orders`, cascata na exclusão)
- `product_id` (BIGINT, FK para `products`)
- `quantity` (INT, não nulo): Quantidade solicitada.
- `price` (DECIMAL(10,2), não nulo): Preço unitário negociado.
- `received_quantity` (INT, default 0): Quantidade conferida no check-in.

### Tabela: `inventory_sessions` (Inventários Físicos)
Sessões de auditoria física de estoque por almoxarifado.
- `id` (BIGINT, chave primária, auto-incremento)
- `warehouse_id` (BIGINT, FK para `warehouses`)
- `status` (ENUM('Em_Andamento', 'Finalizado', 'Cancelado'), default 'Em_Andamento')
- `description` (VARCHAR(255), não nulo)
- `created_by` (BIGINT, FK para `users`)
- `completed_at` (DATETIME, nulo)
- `timestamps`

### Tabela: `inventory_items` (Contagem de Inventários)
Registros de auditoria de cada produto auditado na sessão.
- `id` (BIGINT, chave primária, auto-incremento)
- `inventory_session_id` (BIGINT, FK para `inventory_sessions`, cascata na exclusão)
- `product_id` (BIGINT, FK para `products`)
- `expected_quantity` (INT): Saldo teórico no sistema no início da contagem.
- `counted_quantity` (INT, nulo): Quantidade física contada pelo auditor.
- `adjusted_quantity` (INT, nulo): Delta resultante (contado - esperado).
