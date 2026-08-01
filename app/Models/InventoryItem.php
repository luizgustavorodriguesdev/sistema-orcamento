<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_session_id',
        'product_id',
        'expected_quantity',
        'counted_quantity',
        'adjusted_quantity',
    ];

    protected $casts = [
        'expected_quantity' => 'integer',
        'counted_quantity' => 'integer',
        'adjusted_quantity' => 'integer',
    ];

    /**
     * Sessão de inventário pertencente.
     */
    public function inventorySession(): BelongsTo
    {
        return $this->belongsTo(InventorySession::class);
    }

    /**
     * Produto auditado.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
