<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'cost_price',
        'track_stock',
        'stock_quantity',
        'minimum_stock',
        'promotional_price',
        'category_id',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'show_in_highlighted_menu',
    ];

    protected $casts = [
        'track_stock' => 'boolean',
        'stock_quantity' => 'integer',
        'minimum_stock' => 'integer',
        'cost_price' => 'decimal:2',
        'show_in_highlighted_menu' => 'boolean',
    ];
    
    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     * * [A CORREÇÃO ESTÁ AQUI]
     * Esta linha força o Laravel a incluir sempre o resultado do nosso acessor 'getMainImageAttribute'
     * quando o produto é enviado para o frontend. Isto garante que 'product.main_image' nunca estará em falta.
     */
    protected $appends = ['main_image'];

    /**
     * RELAÇÃO: Um produto pertence a uma categoria.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * RELAÇÃO: Um produto tem muitas imagens.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }
    
    /**
     * RELAÇÃO: Um produto tem muitas escalas de preços.
     */
    public function priceTiers(): HasMany
    {
        return $this->hasMany(PriceTier::class);
    }
    
    /**
     * ACESSOR: Obtém a imagem principal do produto.
     * Isto cria um atributo virtual 'main_image' no nosso objeto produto.
     */
    public function getMainImageAttribute()
    {
        // Procura e retorna a primeira imagem marcada como principal.
        // O 'first()' retorna null se não encontrar, o que é tratado no frontend.
        return $this->images()->where('is_main', true)->first();
    }

    public function themes(): BelongsToMany
    {
        return $this->belongsToMany(Theme::class, 'product_theme');
    }

    public function personalizationTypes(): BelongsToMany
    {
        return $this->belongsToMany(PersonalizationType::class, 'product_personalization_type');
    }

    public function colors(): BelongsToMany
    {
        return $this->belongsToMany(Color::class, 'product_color');
    }

    public function characteristics(): BelongsToMany
    {
        return $this->belongsToMany(Characteristic::class, 'product_characteristic');
    }

    /**
     * Depósitos onde este produto é armazenado.
     */
    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(Warehouse::class, 'product_warehouse')
            ->withPivot('quantity')
            ->withTimestamps();
    }
}