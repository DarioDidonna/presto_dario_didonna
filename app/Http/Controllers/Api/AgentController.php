<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Agent\ArticleResource;
use App\Models\Article;
use App\Models\Category;
use App\Support\ArticleFilters;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

/**
 * API interne in sola lettura usate dai tool dell'agente AI. Mostrano solo annunci pubblicati.
 */
class AgentController extends Controller
{
    public const MAX_RESULTS = 10;

    public function categories(Request $request): JsonResponse
    {
        $this->setLocale($request);

        $categories = Category::withCount(['articles as published_count' => fn ($query) => $query->accepted()])
            ->orderBy('name')
            ->get()
            ->map(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->translated_name,
                'published_count' => $category->published_count,
            ]);

        return response()->json(['categories' => $categories]);
    }

    // Stessi parametri della pagina "Tutti gli annunci" (query, category, min_price, max_price, sort) + limit
    public function search(Request $request): JsonResponse
    {
        $this->setLocale($request);

        $filters = ArticleFilters::fromRequest($request);
        $limit = $request->integer('limit', 5);
        $limit = $limit < 1 ? 5 : min($limit, self::MAX_RESULTS);
        $articles = $filters->query()->paginate($limit);

        return response()->json([
            'total' => $articles->total(),
            'filters' => $filters->toArray(),
            'articles' => ArticleResource::collection($articles->getCollection()),
        ]);
    }

    public function show(Request $request, Article $article): JsonResponse
    {
        $this->setLocale($request);

        // Annunci in revisione o rifiutati: per l'agente non esistono
        abort_unless($article->is_accepted === true && ! $article->isSold(), 404);

        $article->load(['images', 'category', 'user']);

        return response()->json(['article' => (new ArticleResource($article))->detailed()]);
    }

    // Lingua dei nomi delle categorie (?lang=it|en|es), come scelta dall'utente nella chat
    private function setLocale(Request $request): void
    {
        $lang = $request->query('lang');
        App::setLocale(in_array($lang, config('app.available_locales'), true) ? $lang : 'it');
    }
}
