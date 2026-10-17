<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequest;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    // Autorizzazione sulla rotta: can:view,purchaseRequest (solo acquirente e venditore)
    public function show(Request $request, PurchaseRequest $purchaseRequest)
    {
        $purchaseRequest->load(['buyer', 'seller', 'items.article.images']);
        $perspective = $request->user()->id === $purchaseRequest->buyer_id ? 'buyer' : 'seller';

        return view('conversations.show', compact('purchaseRequest', 'perspective'));
    }
}
