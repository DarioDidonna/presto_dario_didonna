<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Il prezzo era un float a precisione singola (~7 cifre significative): 999.999,99 €, il massimo ammesso dal
     * modulo, veniva salvato come 1.000.000 €. Trovato dall'analisi statica (Larastan) sulla migrazione originale.
     */
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->change();
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->float('price', 8)->change();
        });
    }
};
