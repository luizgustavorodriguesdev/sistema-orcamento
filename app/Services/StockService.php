<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Quote;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\PurchaseOrder;
use App\Models\InventorySession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Obtém ou cria o depósito padrão do sistema.
     */
    public static function getDefaultWarehouseId(): int
    {
        $warehouse = Warehouse::where('is_active', true)->first();
        if (!$warehouse) {
            $warehouse = Warehouse::create([
                'name' => 'Depósito Principal',
                'code' => 'DEP-01',
                'description' => 'Depósito padrão criado pelo sistema',
                'is_active' => true,
            ]);
        }
        return $warehouse->id;
    }

    /**
     * Realiza um ajuste genérico de estoque em um depósito específico.
     */
    public static function adjustStock(
        Product $product,
        int $quantity,
        string $type,
        ?string $description = null,
        ?int $quoteId = null,
        ?int $userId = null,
        ?int $warehouseId = null,
        ?int $purchaseOrderId = null,
        ?int $inventorySessionId = null
    ): void {
        if (!$product->track_stock) {
            return;
        }

        if (!$warehouseId) {
            $warehouseId = self::getDefaultWarehouseId();
        }

        // 1. Atualiza ou cria o saldo no depósito específico
        $warehouseRelation = $product->warehouses()->where('warehouse_id', $warehouseId)->first();
        if ($warehouseRelation) {
            $newQty = $warehouseRelation->pivot->quantity + $quantity;
            $product->warehouses()->updateExistingPivot($warehouseId, ['quantity' => $newQty]);
        } else {
            $newQty = $quantity;
            $product->warehouses()->attach($warehouseId, ['quantity' => $newQty]);
        }

        // 2. Consolida o estoque total no campo stock_quantity do produto
        $totalStock = $product->warehouses()->sum('quantity');
        $product->stock_quantity = $totalStock;
        $product->save();

        // 3. Registra a movimentação detalhada
        StockMovement::create([
            'product_id' => $product->id,
            'user_id' => $userId ?? Auth::id(),
            'warehouse_id' => $warehouseId,
            'quote_id' => $quoteId,
            'purchase_order_id' => $purchaseOrderId,
            'inventory_session_id' => $inventorySessionId,
            'quantity' => $quantity,
            'type' => $type,
            'description' => $description,
        ]);
    }

    /**
     * Deduz o estoque quando um orçamento é aprovado.
     */
    public static function deductStockForQuote(Quote $quote): void
    {
        $quote->load('products');

        foreach ($quote->products as $product) {
            if ($product->track_stock) {
                $qty = $product->pivot->quantity;
                self::adjustStock(
                    $product,
                    -$qty,
                    'quote_approved',
                    "Dedução automática do Orçamento #" . str_pad($quote->id, 4, '0', STR_PAD_LEFT),
                    $quote->id
                );
            }
        }
    }

    /**
     * Devolve o estoque quando um orçamento aprovado é cancelado/retornado.
     */
    public static function replenishStockForQuote(Quote $quote): void
    {
        $quote->load('products');

        foreach ($quote->products as $product) {
            if ($product->track_stock) {
                $qty = $product->pivot->quantity;
                self::adjustStock(
                    $product,
                    $qty,
                    'quote_cancelled',
                    "Estorno automático do Orçamento #" . str_pad($quote->id, 4, '0', STR_PAD_LEFT),
                    $quote->id
                );
            }
        }
    }

    /**
     * Sincroniza o estoque após a edição de itens em um orçamento já Aprovado.
     */
    public static function syncStockForQuoteUpdate(Quote $quote, array $newItems): void
    {
        $quote->load('products');
        
        $oldItems = $quote->products->pluck('pivot.quantity', 'id')->all();

        foreach ($newItems as $productId => $itemData) {
            $product = Product::find($productId);
            if (!$product || !$product->track_stock) {
                continue;
            }

            $newQty = $itemData['quantity'];
            
            if (array_key_exists($productId, $oldItems)) {
                $oldQty = $oldItems[$productId];
                $delta = $newQty - $oldQty;

                if ($delta !== 0) {
                    self::adjustStock(
                        $product,
                        -$delta,
                        'adjustment',
                        "Ajuste por alteração de quantidade no Orçamento #" . str_pad($quote->id, 4, '0', STR_PAD_LEFT),
                        $quote->id
                    );
                }
                unset($oldItems[$productId]);
            } else {
                self::adjustStock(
                    $product,
                    -$newQty,
                    'adjustment',
                    "Inclusão de item no Orçamento #" . str_pad($quote->id, 4, '0', STR_PAD_LEFT),
                    $quote->id
                );
            }
        }

        foreach ($oldItems as $productId => $oldQty) {
            $product = Product::find($productId);
            if ($product && $product->track_stock) {
                self::adjustStock(
                    $product,
                    $oldQty,
                    'adjustment',
                    "Remoção de item no Orçamento #" . str_pad($quote->id, 4, '0', STR_PAD_LEFT),
                    $quote->id
                );
            }
        }
    }

    /**
     * Executa o recebimento/check-in físico das mercadorias de uma ordem de compra.
     */
    public static function receivePurchaseOrder(PurchaseOrder $po, array $itemsReceived, int $warehouseId): void
    {
        DB::transaction(function () use ($po, $itemsReceived, $warehouseId) {
            $po->load('items.product');

            foreach ($po->items as $item) {
                $receivedQty = $itemsReceived[$item->product_id] ?? 0;

                if ($receivedQty > 0) {
                    // 1. Atualizar quantidade recebida na ordem
                    $item->received_quantity += $receivedQty;
                    $item->save();

                    // 2. Incrementar estoque do produto no depósito
                    self::adjustStock(
                        $item->product,
                        $receivedQty,
                        'inbound_po',
                        "Recebimento de compra - O.C. #{$po->po_number}",
                        null,
                        Auth::id(),
                        $warehouseId,
                        $po->id
                    );
                }
            }

            // Checar se a ordem de compra foi totalmente recebida
            $po->refresh();
            $fullyReceived = true;
            foreach ($po->items as $item) {
                if ($item->received_quantity < $item->quantity) {
                    $fullyReceived = false;
                    break;
                }
            }

            if ($fullyReceived) {
                $po->status = 'Recebido';
                $po->received_at = now();
            } else {
                $po->status = 'Recebido_Parcial';
            }
            $po->save();
        });
    }

    /**
     * Conclui a reconciliação de uma sessão de inventário e ajusta os saldos.
     */
    public static function reconcileInventory(InventorySession $session): void
    {
        DB::transaction(function () use ($session) {
            $session->load('items.product');

            foreach ($session->items as $item) {
                if ($item->counted_quantity === null) {
                    continue;
                }

                $delta = $item->counted_quantity - $item->expected_quantity;
                $item->adjusted_quantity = $delta;
                $item->save();

                if ($delta !== 0) {
                    // Aplica o ajuste de estoque corretivo no depósito do inventário
                    self::adjustStock(
                        $item->product,
                        $delta,
                        'inventory_adjustment',
                        "Correção por inventário - Ref: {$session->description}",
                        null,
                        Auth::id(),
                        $session->warehouse_id,
                        null,
                        $session->id
                    );
                }
            }

            $session->status = 'Finalizado';
            $session->completed_at = now();
            $session->save();
        });
    }
}
