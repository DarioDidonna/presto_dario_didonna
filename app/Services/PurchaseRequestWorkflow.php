<?php

namespace App\Services;

use App\Models\Article;
use App\Models\PurchaseRequest;
use App\Notifications\PurchaseRequestAnswered;
use App\Notifications\PurchaseRequestCancelled;
use Illuminate\Support\Facades\DB;

/**
 * Cambi di stato di una richiesta d'acquisto e loro effetto sugli annunci.
 *
 *   accetta  -> annunci venduti (escono dagli elenchi e dai carrelli)
 *   rifiuta  -> annunci di nuovo disponibili
 *   annulla  -> annunci di nuovo disponibili (azione dell'acquirente)
 */
class PurchaseRequestWorkflow
{
    public function accept(PurchaseRequest $request): void
    {
        $this->transition($request, PurchaseRequest::ACCEPTED, function (array $articleIds) {
            Article::whereKey($articleIds)->update(['sold_at' => now(), 'reserved_at' => null]);
            // Venduti: spariscono anche dai carrelli di chi li aveva aggiunti prima della prenotazione
            DB::table('cart_items')->whereIn('article_id', $articleIds)->delete();
        });

        $request->buyer->notify(new PurchaseRequestAnswered($request, accepted: true));
    }

    public function decline(PurchaseRequest $request): void
    {
        $this->transition($request, PurchaseRequest::DECLINED, fn (array $ids) => $this->release($ids));

        $request->buyer->notify(new PurchaseRequestAnswered($request, accepted: false));
    }

    public function cancel(PurchaseRequest $request): void
    {
        $this->transition($request, PurchaseRequest::CANCELLED, fn (array $ids) => $this->release($ids));

        $request->seller->notify(new PurchaseRequestCancelled($request));
    }

    private function release(array $articleIds): void
    {
        Article::whereKey($articleIds)->whereNull('sold_at')->update(['reserved_at' => null]);
    }

    /**
     * La richiesta viene riletta con un blocco: se venditore e acquirente agiscono nello stesso istante
     * (es. accetta e annulla) vince il primo, il secondo trova la richiesta non più in attesa.
     */
    private function transition(PurchaseRequest $request, string $status, callable $effect): void
    {
        DB::transaction(function () use ($request, $status, $effect) {
            $locked = PurchaseRequest::whereKey($request->id)->lockForUpdate()->first();

            if (! $locked?->isPending()) {
                throw new PurchaseRequestNotPendingException(__('ui.request_not_pending'));
            }

            $locked->update(['status' => $status]);
            $effect($locked->items()->whereNotNull('article_id')->pluck('article_id')->all());
        });

        $request->refresh();
    }
}
