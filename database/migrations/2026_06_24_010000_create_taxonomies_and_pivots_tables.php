<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Criar tabelas principais
        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('personalization_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('colors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('hex_code')->nullable();
            $table->timestamps();
        });

        Schema::create('characteristics', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        // 2. Criar tabelas pivô
        Schema::create('product_theme', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('theme_id')->constrained()->onDelete('cascade');
            $table->unique(['product_id', 'theme_id']);
        });

        Schema::create('product_personalization_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('personalization_type_id')->constrained('personalization_types')->onDelete('cascade');
            $table->unique(['product_id', 'personalization_type_id'], 'prod_pers_type_unique');
        });

        Schema::create('product_color', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('color_id')->constrained()->onDelete('cascade');
            $table->unique(['product_id', 'color_id']);
        });

        Schema::create('product_characteristic', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('characteristic_id')->constrained()->onDelete('cascade');
            $table->unique(['product_id', 'characteristic_id']);
        });

        // 3. Migrar dados antigos
        $products = DB::table('products')->get();
        foreach ($products as $product) {
            if (!empty($product->themes)) {
                $themes = array_filter(array_map('trim', explode(',', $product->themes)));
                foreach ($themes as $themeName) {
                    $slug = Str::slug($themeName);
                    $themeId = DB::table('themes')->where('slug', $slug)->value('id');
                    if (!$themeId) {
                        $themeId = DB::table('themes')->insertGetId([
                            'name' => $themeName,
                            'slug' => $slug,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                    DB::table('product_theme')->insertOrIgnore([
                        'product_id' => $product->id,
                        'theme_id' => $themeId,
                    ]);
                }
            }

            if (!empty($product->personalization_types)) {
                $persTypes = array_filter(array_map('trim', explode(',', $product->personalization_types)));
                foreach ($persTypes as $persName) {
                    $slug = Str::slug($persName);
                    $persId = DB::table('personalization_types')->where('slug', $slug)->value('id');
                    if (!$persId) {
                        $persId = DB::table('personalization_types')->insertGetId([
                            'name' => $persName,
                            'slug' => $slug,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                    DB::table('product_personalization_type')->insertOrIgnore([
                        'product_id' => $product->id,
                        'personalization_type_id' => $persId,
                    ]);
                }
            }

            if (!empty($product->colors)) {
                $colors = array_filter(array_map('trim', explode(',', $product->colors)));
                foreach ($colors as $colorName) {
                    $slug = Str::slug($colorName);
                    $colorId = DB::table('colors')->where('slug', $slug)->value('id');
                    if (!$colorId) {
                        $colorId = DB::table('colors')->insertGetId([
                            'name' => $colorName,
                            'slug' => $slug,
                            'hex_code' => $this->guessHexCode($colorName),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                    DB::table('product_color')->insertOrIgnore([
                        'product_id' => $product->id,
                        'color_id' => $colorId,
                    ]);
                }
            }

            if (!empty($product->characteristics)) {
                $chars = array_filter(array_map('trim', explode(',', $product->characteristics)));
                foreach ($chars as $charName) {
                    $slug = Str::slug($charName);
                    $charId = DB::table('characteristics')->where('slug', $slug)->value('id');
                    if (!$charId) {
                        $charId = DB::table('characteristics')->insertGetId([
                            'name' => $charName,
                            'slug' => $slug,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                    DB::table('product_characteristic')->insertOrIgnore([
                        'product_id' => $product->id,
                        'characteristic_id' => $charId,
                    ]);
                }
            }
        }

        // 4. Remover colunas antigas da tabela products
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['themes', 'personalization_types', 'colors', 'characteristics']);
        });
    }

    /**
     * Helper para adivinhar a cor (hex) caso seja padrão em português
     */
    private function guessHexCode(string $colorName): ?string
    {
        $colors = [
            'preto' => '#000000',
            'branco' => '#ffffff',
            'vermelho' => '#ef4444',
            'azul' => '#3b82f6',
            'verde' => '#22c55e',
            'amarelo' => '#eab308',
            'rosa' => '#ec4899',
            'roxo' => '#a855f7',
            'cinza' => '#6b7280',
            'laranja' => '#f97316',
            'marrom' => '#78350f',
            'dourado' => '#d97706',
            'prateado' => '#9ca3af',
        ];

        $lower = Str::lower($colorName);
        foreach ($colors as $name => $hex) {
            if (Str::contains($lower, $name)) {
                return $hex;
            }
        }

        return null;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Adicionar de volta as colunas
        Schema::table('products', function (Blueprint $table) {
            $table->string('themes')->nullable();
            $table->string('personalization_types')->nullable();
            $table->string('colors')->nullable();
            $table->string('characteristics')->nullable();
        });

        // Apagar as novas tabelas
        Schema::dropIfExists('product_theme');
        Schema::dropIfExists('product_personalization_type');
        Schema::dropIfExists('product_color');
        Schema::dropIfExists('product_characteristic');
        Schema::dropIfExists('themes');
        Schema::dropIfExists('personalization_types');
        Schema::dropIfExists('colors');
        Schema::dropIfExists('characteristics');
    }
};
