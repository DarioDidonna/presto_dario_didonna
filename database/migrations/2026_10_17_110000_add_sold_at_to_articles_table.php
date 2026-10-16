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
        Schema::table('articles', function (Blueprint $table) {
            // Venduto: il venditore ha accettato una richiesta d'acquisto. Esce dagli elenchi pubblici
            $table->timestamp('sold_at')->nullable()->after('reserved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', fn (Blueprint $table) => $table->dropColumn('sold_at'));
    }
};
