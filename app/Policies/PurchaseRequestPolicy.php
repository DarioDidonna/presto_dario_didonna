<?php

namespace App\Policies;

use App\Models\PurchaseRequest;
use App\Models\User;

class PurchaseRequestPolicy
{
    // La conversazione la vedono solo acquirente e venditore
    public function view(User $user, PurchaseRequest $request): bool
    {
        return $request->involves($user);
    }

    public function message(User $user, PurchaseRequest $request): bool
    {
        return $request->involves($user) && $request->acceptsMessages();
    }

    // Recensione del venditore: solo l'acquirente, solo dopo una vendita accettata, una volta sola
    public function review(User $user, PurchaseRequest $request): bool
    {
        return $user->id === $request->buyer_id
            && $request->status === PurchaseRequest::ACCEPTED
            // Relazione già caricata nelle liste (niente query per ogni scheda)
            && ($request->relationLoaded('review') ? $request->review === null : ! $request->review()->exists());
    }

    // Il venditore accetta o rifiuta, solo finché la richiesta è in attesa
    public function respond(User $user, PurchaseRequest $request): bool
    {
        return $user->id === $request->seller_id && $request->isPending();
    }

    // L'acquirente può annullare, solo finché la richiesta è in attesa
    public function cancel(User $user, PurchaseRequest $request): bool
    {
        return $user->id === $request->buyer_id && $request->isPending();
    }
}
