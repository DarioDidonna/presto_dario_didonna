<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Google Vision rimosso dal progetto: niente più etichette né valutazioni SafeSearch delle foto
    public function up(): void
    {
        Schema::table('images', function (Blueprint $table) {
            $table->dropColumn(['labels', 'adult', 'spoof', 'medical', 'violence', 'racy']);
        });
    }

    public function down(): void
    {
        Schema::table('images', function (Blueprint $table) {
            $table->text('labels')->nullable();
            $table->string('adult')->nullable();
            $table->string('spoof')->nullable();
            $table->string('medical')->nullable();
            $table->string('violence')->nullable();
            $table->string('racy')->nullable();
        });
    }
};
