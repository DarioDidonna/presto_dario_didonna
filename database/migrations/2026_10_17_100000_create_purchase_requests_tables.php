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
        // Una richiesta d'acquisto per ogni venditore: nessun pagamento online, si accordano tra loro
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 20)->default('pending'); // pending, accepted, declined, cancelled
            $table->text('message')->nullable();
            $table->decimal('total', 10, 2);
            $table->timestamps();

            $table->index(['buyer_id', 'created_at']);
            $table->index(['seller_id', 'status']);
        });

        Schema::create('purchase_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_request_id')->constrained()->cascadeOnDelete();
            // Annuncio eliminato in seguito: la richiesta resta nello storico con titolo e prezzo salvati
            $table->foreignId('article_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->decimal('price', 10, 2); // prezzo al momento della richiesta
            $table->timestamps();
        });

        Schema::table('articles', function (Blueprint $table) {
            // Riservato da una richiesta d'acquisto in attesa: non può essere aggiunto ad altri carrelli
            $table->timestamp('reserved_at')->nullable()->after('is_accepted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', fn (Blueprint $table) => $table->dropColumn('reserved_at'));
        Schema::dropIfExists('purchase_request_items');
        Schema::dropIfExists('purchase_requests');
    }
};
