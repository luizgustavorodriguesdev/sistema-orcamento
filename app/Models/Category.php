<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'show_in_highlighted_menu',
        'parent_id',
        'mega_menu_banner_path',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'show_in_highlighted_menu' => 'boolean',
        'parent_id' => 'integer',
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

    /**
     * RELAÇÃO: Uma categoria pode pertencer a uma categoria pai (subcategoria).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * RELAÇÃO: Uma categoria pai pode ter várias categorias filhas (subcategorias).
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('name');
    }
}
