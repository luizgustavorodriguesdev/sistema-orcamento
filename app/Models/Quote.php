<?php
// app/Models/Quote.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Client;

class Quote extends Model
{
    use HasFactory;

    protected $fillable = [
        'unique_hash',
        'client_id',
        'user_id',
        'status',
        'total_amount',
        'payment_terms',
        'delivery_info',
        // --- Novos campos que adicionamos na última migração ---
        'customer_phone',
        'cep',
        'address_street',
        'address_neighborhood',
        'address_city',
        'address_state',
        'customization_details',
    ];

    protected $appends = [
        'client_name',
        'client_contact',
    ];

    public function getClientNameAttribute()
    {
        return $this->client ? $this->client->name : null;
    }

    public function getClientContactAttribute()
    {
        return $this->client ? $this->client->contact_main : null;
    }


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