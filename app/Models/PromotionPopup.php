<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionPopup extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'image_path',
        'start_date',
        'end_date',
        'button_text',
        'button_link',
        'has_countdown',
        'countdown_end',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'countdown_end' => 'datetime',
        'has_countdown' => 'boolean',
        'is_active' => 'boolean',
    ];
}
