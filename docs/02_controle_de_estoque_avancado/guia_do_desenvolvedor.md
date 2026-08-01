# Guia do Desenvolvedor - Controle de Estoque Avançado (ERP)

Este guia documenta a arquitetura de código, estruturas de classes, controllers e fluxos Inertia implementados no módulo de estoque avançado.

---

## 1. Estrutura de Models e Relações

### Model `Product`
- Relacionamento Many-to-Many com `Warehouse` através da tabela pivot `product_warehouse` para controle de saldo fracionado:
  ```php
  public function warehouses()
  {
      return $this->belongsToMany(Warehouse::class, 'product_warehouse')
                  ->withPivot('quantity')
                  ->withTimestamps();
  }
  ```
- Método helper para estoque agregado total:
  ```php
  public function getTotalStockAttribute()
  {
      return $this->warehouses()->sum('quantity');
  }
  ```

### Model `StockMovement`
- Vinculado às novas entidades de negócios:
  ```php
  public function warehouse() { return $this->belongsTo(Warehouse::class); }
  public function purchaseOrder() { return $this->belongsTo(PurchaseOrder::class); }
  public function inventorySession() { return $this->belongsTo(InventorySession::class); }
  ```

---

## 2. Core Service: `StockService`

A classe `App\Services\StockService` é a responsável isolada pelas transações de entrada e saída, prevenindo concorrência e mantendo a integridade referencial:

- `adjustStock($product, $quantity, $warehouseId, $type, $description, $purchaseOrderId = null, $inventorySessionId = null)`:
  - Cria ou atualiza o saldo na tabela pivot `product_warehouse`.
  - Registra a movimentação em `stock_movements`.
- `receivePurchaseOrder(PurchaseOrder $po, array $receivedQuantities)`:
  - Loop pelos itens da ordem de compra.
  - Incrementa a quantidade recebida em pivot e gera movimentações com tipo `inbound_po`.
- `reconcileInventory(InventorySession $session)`:
  - Compara a contagem informada contra o estoque esperado.
  - Gera ajustes delta positivos/negativos na tabela pivot.
  - Fecha a sessão de inventário.

---

## 3. Endpoints e Fluxo do Frontend

### Rotas Administradas (`routes/web.php`)
- `/suppliers` -> `SupplierController`
- `/warehouses` -> `WarehouseController`
- `/purchase-orders` -> `PurchaseOrderController`
- `/purchase-orders/{po}/checkin` -> `PurchaseOrderController@checkin`
- `/stock/suggestions` -> `PurchaseOrderController@suggestions`
- `/inventory` -> `InventoryController`

### Views Vue 3 (Inertia)
- **`Stock/Index.vue`**: Centraliza a listagem de saldos detalhados, o formulário de lançamentos manuais estruturados e a visão financeira (Custo vs. Venda).
- **`PurchaseOrders/CheckIn.vue`**: Interface reativa contendo leitores ou inputs de quantidades recebidas para entrada direta em depósitos específicos.
- **`Inventory/Show.vue`**: Exibe a lista de itens com inputs reativos de contagem, calculando deltas instantaneamente no frontend.
