<?php

namespace App\Policies;

use App\Models\PurchaseRequest;
use App\Models\User;

class PurchaseRequestPolicy
{
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
