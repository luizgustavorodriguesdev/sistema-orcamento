<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'warehouse_id',
        'quote_id',
        'purchase_order_id',
        'inventory_session_id',
        'quantity',
        'type', // addition, subtraction, adjustment, quote_approved, quote_cancelled, inbound_po, inventory_adjustment
        'description',
    ];

    /**
     * RELAÇÃO: Um registro de movimentação pertence a um produto.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * RELAÇÃO: Um registro de movimentação pertence ao usuário que a realizou.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * RELAÇÃO: Um registro de movimentação pode pertencer a um orçamento.
     */
    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    /**
     * RELAÇÃO: Um registro de movimentação pertence a um depósito.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * RELAÇÃO: Um registro de movimentação pode pertencer a uma ordem de compra.
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * RELAÇÃO: Um registro de movimentação pode pertencer a um inventário.
     */
    public function inventorySession(): BelongsTo
    {
        return $this->belongsTo(InventorySession::class);
    }
}
