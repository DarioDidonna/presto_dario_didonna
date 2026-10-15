<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function homepage()
    {
        $articles = Article::accepted()->withCardRelations()->latest()->take(6)->get();

        return view('homepage', compact('articles'));
    }

    public function searchArticles(Request $request)
    {
        $query = $request->input('query');

        // TNTSearch va in errore con query vuote o non testuali (es. ?query[]=x)
        if (! is_string($query) || trim($query) === '') {
            return redirect()->route('article.index');
        }
        $query = mb_substr(trim($query), 0, 100);

        $articles = Article::search($query)
            ->query(fn ($builder) => $builder->accepted()->withCardRelations())
            ->paginate(10)
            ->withQueryString();

        return view('article.searched', [
            'articles' => $articles,
            'query' => $query,
        ]);
    }

    public function setLanguage(Request $request, string $lang)
    {
        session()->put('locale', $lang);
        // Salvata anche sull'utente: serve per le email e per ripristinarla al prossimo login
        $request->user()?->forceFill(['locale' => $lang])->save();

        return redirect()->back();
    }
}
