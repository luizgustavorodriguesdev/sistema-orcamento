<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    protected $table = 'media';

    protected $fillable = [
        'filename',
        'path',
        'mime_type',
        'size',
        'title',
        'alt_text',
        'caption',
        'description',
    ];

    protected $appends = ['url'];

    /**
     * Accessor para retornar a URL pública da mídia.
     */
    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->path);
    }
}
