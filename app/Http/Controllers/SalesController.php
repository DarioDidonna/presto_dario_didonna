<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequest;
use App\Services\PurchaseRequestNotPendingException;
use App\Services\PurchaseRequestWorkflow;
use Illuminate\Http\Request;

/**
 * Lato venditore: richieste d'acquisto ricevute.
 */
class SalesController extends Controller
{
    public function index(Request $request)
    {
        $requests = $request->user()->receivedPurchaseRequests()
            ->with(['buyer', 'items.article.images'])
            ->withUnreadMessagesCount($request->user())
            // Prima quelle in attesa di risposta, poi le altre dalla più recente
            ->orderByRaw('case when status = ? then 0 else 1 end', [PurchaseRequest::PENDING])
            ->latest()
            ->get();

        return view('sales.index', compact('requests'));
    }

    // Autorizzazione sulla rotta: can:respond,purchaseRequest (venditore e richiesta in attesa)
    public function accept(PurchaseRequest $purchaseRequest, PurchaseRequestWorkflow $workflow)
    {
        return $this->run(fn () => $workflow->accept($purchaseRequest), __('ui.sales_accepted', ['name' => $purchaseRequest->buyer->name]));
    }

    public function decline(PurchaseRequest $purchaseRequest, PurchaseRequestWorkflow $workflow)
    {
        return $this->run(fn () => $workflow->decline($purchaseRequest), __('ui.sales_declined'));
    }

    private function run(callable $action, string $message)
    {
        try {
            $action();
        } catch (PurchaseRequestNotPendingException $error) {
            return redirect()->route('sales.index')->with('warning', $error->getMessage());
        }

        return redirect()->route('sales.index')->with('message', $message);
    }
}
