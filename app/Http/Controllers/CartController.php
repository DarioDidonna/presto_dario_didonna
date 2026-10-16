<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $articles = $user->cart()->with(['images', 'category', 'user'])->get();

        // Annunci non più acquistabili (rifiutati, tornati in revisione, riservati da altri): tolti con un avviso
        $unavailable = $articles->reject(fn (Article $article) => $article->isAvailable());
        if ($unavailable->isNotEmpty()) {
            $user->cart()->detach($unavailable->modelKeys());
            $articles = $articles->diff($unavailable);
            session()->now('warning', trans_choice('ui.cart_items_removed', $unavailable->count(), ['count' => $unavailable->count()]));
        }

        // Un gruppo per venditore: ogni venditore riceverà la sua richiesta d'acquisto
        $groups = $articles->groupBy('user_id')->map(fn ($items) => [
            'seller' => $items->first()->user,
            'articles' => $items,
            'subtotal' => $items->sum(fn (Article $article) => (float) $article->price),
        ])->values();

        return view('cart.index', [
            'groups' => $groups,
            'count' => $articles->count(),
            'total' => $groups->sum('subtotal'),
        ]);
    }

    // Autorizzazione sulla rotta: can:addToCart,article (pubblicato e non proprio)
    public function add(Request $request, Article $article)
    {
        $request->user()->cart()->syncWithoutDetaching([$article->id]);

        return back()->with('message', __('ui.cart_added', ['title' => $article->title]));
    }

    public function remove(Request $request, Article $article)
    {
        $request->user()->cart()->detach($article->id);

        return back()->with('message', __('ui.cart_removed', ['title' => $article->title]));
    }

    public function clear(Request $request)
    {
        $request->user()->cart()->detach();

        return redirect()->route('cart.index')->with('message', __('ui.cart_cleared'));
    }
}
