<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Color extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'hex_code'];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($color) {
            if (empty($color->slug)) {
                $color->slug = Str::slug($color->name);
            }
        });
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_color');
    }
}
