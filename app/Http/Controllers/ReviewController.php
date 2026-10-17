<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Models\PurchaseRequest;
use App\Notifications\ReviewReceived;
use Illuminate\Database\UniqueConstraintViolationException;

class ReviewController extends Controller
{
    // Autorizzazione sulla rotta: can:review,purchaseRequest (acquirente, richiesta accettata, nessuna recensione)
    public function store(StoreReviewRequest $request, PurchaseRequest $purchaseRequest)
    {
        try {
            $review = $purchaseRequest->review()->make($request->validated())->forceFill([
                'reviewer_id' => $purchaseRequest->buyer_id,
                'seller_id' => $purchaseRequest->seller_id,
            ]);
            $review->save();
        } catch (UniqueConstraintViolationException) {
            // Doppio invio quasi simultaneo: la policy non può vederlo, l'indice unico sì
            return redirect()->route('orders.index')->with('warning', __('ui.reviews_already'));
        }

        $purchaseRequest->seller?->notify(new ReviewReceived($review));

        return redirect()->route('orders.index')->with('message', __('ui.reviews_thanks'));
    }
}
