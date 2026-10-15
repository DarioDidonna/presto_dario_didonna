<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class ArticleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth', only: ['create', 'edit', 'destroy']),
        ];
    }

    public function create()
    {
        return view('article.create');
    }

    public function index()
    {
        $articles = Article::accepted()->withCardRelations()->latest()->paginate(10);

        return view('article.index', compact('articles'));
    }

    public function show(Article $article)
    {
        // 404 (non 403) per non rivelare l'esistenza di annunci non pubblicati: regola in ArticlePolicy::view
        abort_unless(Gate::allows('view', $article), 404);

        $article->load(['images', 'category', 'user']);

        return view('article.show', compact('article'));
    }

    public function edit(Article $article)
    {
        Gate::authorize('update', $article);

        return view('article.edit', compact('article'));
    }

    public function destroy(Article $article)
    {
        Gate::authorize('delete', $article);

        // Le righe di "images" spariscono per il cascade del DB; i file vanno rimossi a mano
        Storage::disk('public')->deleteDirectory("articles/{$article->id}");
        $article->delete();

        return redirect()->route('profile.show')->with('message', __('ui.article_deleted', ['title' => $article->title]));
    }

    public function byCategory(Category $category)
    {
        $articles = $category->articles()->accepted()->withCardRelations()->latest()->paginate(12);

        return view('article.byCategory', compact('articles', 'category'));
    }
}
