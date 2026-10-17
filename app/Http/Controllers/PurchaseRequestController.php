<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequest;
use App\Services\Checkout;
use App\Services\CheckoutException;
use App\Services\PurchaseRequestNotPendingException;
use App\Services\PurchaseRequestWorkflow;
use Illuminate\Http\Request;

class PurchaseRequestController extends Controller
{
    // "I miei ordini": le richieste inviate dall'utente, dalla più recente
    public function index(Request $request)
    {
        $orders = $request->user()->purchaseRequests()
            ->with(['seller', 'items.article.images', 'review'])
            ->withUnreadMessagesCount($request->user())
            ->latest()
            ->get();

        return view('orders.index', compact('orders'));
    }

    // Invio del carrello: una richiesta d'acquisto per ogni venditore
    public function store(Request $request, Checkout $checkout)
    {
        $data = $request->validate(['message' => ['nullable', 'string', 'max:500']]);

        try {
            $requests = $checkout->submit($request->user(), $data['message'] ?? null);
        } catch (CheckoutException $error) {
            return redirect()->route('cart.index')->with('warning', $error->getMessage());
        }

        return redirect()->route('orders.index')
            ->with('message', trans_choice('ui.checkout_done', $requests->count(), ['count' => $requests->count()]));
    }

    // Autorizzazione sulla rotta: can:cancel,purchaseRequest (acquirente e richiesta in attesa)
    public function cancel(PurchaseRequest $purchaseRequest, PurchaseRequestWorkflow $workflow)
    {
        try {
            $workflow->cancel($purchaseRequest);
        } catch (PurchaseRequestNotPendingException $error) {
            return redirect()->route('orders.index')->with('warning', $error->getMessage());
        }

        return redirect()->route('orders.index')->with('message', __('ui.orders_cancelled'));
    }
}
