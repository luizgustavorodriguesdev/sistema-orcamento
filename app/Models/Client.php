<?php
// app/Models/Client.php

namespace App\Models; // <-- A CORREÇÃO ESTÁ AQUI

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_main',
        'contact_secondary',
        'address',
    ];

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }
}