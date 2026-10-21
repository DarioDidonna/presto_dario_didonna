<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Volti oscurati dal rilevatore locale: null = foto non ancora controllata (servizio spento o non configurato)
    public function up(): void
    {
        Schema::table('images', function (Blueprint $table) {
            $table->unsignedSmallInteger('faces_count')->nullable()->after('path');
        });
    }

    public function down(): void
    {
        Schema::table('images', function (Blueprint $table) {
            $table->dropColumn('faces_count');
        });
    }
};
