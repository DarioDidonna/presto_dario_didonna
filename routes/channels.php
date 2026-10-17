<?php

use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Conversazione di una richiesta d'acquisto: stesse regole della pagina (solo acquirente e venditore)
Broadcast::channel('conversations.{purchaseRequest}', function (User $user, PurchaseRequest $purchaseRequest) {
    return $user->can('view', $purchaseRequest);
});
