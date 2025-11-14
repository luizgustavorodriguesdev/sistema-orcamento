<?php
// app/Models/Quote.php

namespace App\Models; // <-- VERIFIQUE SE ESTÁ CORRETO

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Client; // <-- ADICIONE ESTA LINHA

class Quote extends Model
{
    use HasFactory;

    protected $fillable = [
        'unique_hash',
        'client_id', // Adicione esta linha
        'user_id',
        'status',
        'total_amount',
        'payment_terms',
        'delivery_info',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'quote_product')
                    ->withPivot('quantity', 'unit_price')
                    ->withTimestamps();
    }

    /**
     * RELAÇÃO: Um orçamento pertence a um cliente.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}