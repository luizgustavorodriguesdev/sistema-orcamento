<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'promotional_price',
        'category_id',
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
}