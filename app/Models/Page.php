<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'custom_css',
        'is_active',
    ];

    /**
     * O "boot" do modelo para auto-gerar slugs
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($page) {
            if (empty($page->slug) || $page->isDirty('title')) {
                $page->slug = Str::slug($page->title);
            }
        });
    }
}
