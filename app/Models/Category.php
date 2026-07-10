<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    /**
     * Os atributos que podem ser atribuídos em massa.
     */
    protected $fillable = [
        'name',
        'description',
        'is_featured',
        'image_path',
        'slug',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    /**
     * O "boot" do modelo para registrar eventos.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($category) {
            if (empty($category->slug) || $category->isDirty('name')) {
                $category->slug = \Illuminate\Support\Str::slug($category->name);
            }
        });
    }

    /**
     * RELAÇÃO: Uma categoria pode ter muitos produtos.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
