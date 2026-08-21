<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('show_in_highlighted_menu')->default(false);
        });
        Schema::table('pages', function (Blueprint $table) {
            $table->boolean('show_in_highlighted_menu')->default(false);
        });
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('show_in_highlighted_menu')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('show_in_highlighted_menu');
        });
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('show_in_highlighted_menu');
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('show_in_highlighted_menu');
        });
    }
};
