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
        Schema::table('categories', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
        });

        // Gera slugs para as categorias existentes para evitar falhas de restrição
        $categories = DB::table('categories')->get();
        foreach ($categories as $cat) {
            $baseSlug = Str::slug($cat->name);
            $slug = $baseSlug;
            $count = 1;
            
            // Tratamento simples de colisão
            while (DB::table('categories')->where('slug', $slug)->where('id', '!=', $cat->id)->exists()) {
                $slug = $baseSlug . '-' . $count;
                $count++;
            }

            DB::table('categories')
                ->where('id', $cat->id)
                ->update(['slug' => $slug]);
        }

        // Define a coluna como única após o preenchimento dos dados existentes
        Schema::table('categories', function (Blueprint $table) {
            $table->string('slug')->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
