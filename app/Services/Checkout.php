<?php

namespace App\Services;

use App\Models\Article;
use App\Models\PurchaseRequest;
use App\Models\User;
use App\Notifications\PurchaseRequested;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Trasforma il carrello in richieste d'acquisto, una per venditore, e riserva gli annunci.
 */
class Checkout
{
    /**
     * @return Collection<int, PurchaseRequest>
     *
     * @throws CheckoutException se il carrello è vuoto o qualche annuncio non è più disponibile
     */
    public function submit(User $buyer, ?string $message): Collection
    {
        $unavailable = collect();

        $requests = DB::transaction(function () use ($buyer, $message, &$unavailable) {
            $ids = $buyer->cart()->pluck('articles.id');
            if ($ids->isEmpty()) {
                return collect();
            }

            // lockForUpdate: due acquirenti che inviano nello stesso istante non possono riservare lo stesso oggetto
            $articles = Article::whereKey($ids)->lockForUpdate()->get();

            $unavailable = $articles->filter(fn (Article $article) => ! $article->isAvailable() || $article->user_id === $buyer->id);
            if ($unavailable->isNotEmpty()) {
                return collect(); // niente invio parziale
            }

            $requests = $articles->groupBy('user_id')->map(function (Collection $items, int $sellerId) use ($buyer, $message) {
                $request = PurchaseRequest::create([
                    'buyer_id' => $buyer->id,
                    'seller_id' => $sellerId,
                    'status' => PurchaseRequest::PENDING,
                    'message' => $message,
                    'total' => $items->sum(fn (Article $article) => (float) $article->price),
                ]);
                // Titolo e prezzo salvati: la richiesta resta leggibile anche se l'annuncio cambia o viene eliminato
                $request->items()->createMany($items->map(fn (Article $article) => [
                    'article_id' => $article->id,
                    'title' => $article->title,
                    'price' => $article->price,
                ])->all());

                return $request;
            })->values();

            Article::whereKey($articles->modelKeys())->update(['reserved_at' => now()]);
            $buyer->cart()->detach();

            return $requests;
        });

        // Fuori dalla transazione: un'eccezione al suo interno annullerebbe anche la rimozione dal carrello
        if ($unavailable->isNotEmpty()) {
            $buyer->cart()->detach($unavailable->modelKeys());
            throw new CheckoutException(__('ui.checkout_unavailable', ['titles' => $unavailable->pluck('title')->join(', ')]));
        }
        if ($requests->isEmpty()) {
            throw new CheckoutException(__('ui.checkout_empty'));
        }

        // Email ai venditori: in coda, dopo il commit, nella lingua di ciascuno
        $requests->each(fn (PurchaseRequest $request) => $request->seller->notify(new PurchaseRequested($request)));

        return $requests;
    }
}
