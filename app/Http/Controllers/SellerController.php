<?php

namespace App\Http\Controllers;

use App\Models\User;

class SellerController extends Controller
{
    // Profilo pubblico del venditore: niente email né dati personali, solo annunci pubblicati e recensioni
    public function show(User $user)
    {
        $articles = $user->articles()->accepted()->withCardRelations()->latest()
            ->paginate(6, pageName: 'annunci')->withQueryString();
        $reviews = $user->reviewsReceived()->with('reviewer')->latest()
            ->paginate(5, pageName: 'recensioni')->withQueryString();

        return view('sellers.show', [
            'seller' => $user,
            'summary' => $user->reviewSummary(),
            'articles' => $articles,
            'reviews' => $reviews,
        ]);
    }
}
