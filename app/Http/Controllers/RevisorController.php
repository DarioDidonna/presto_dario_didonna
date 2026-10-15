<?php

namespace App\Http\Controllers;

use App\Mail\BecomeRevisor;
use App\Models\Article;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class RevisorController extends Controller
{
    public function index()
    {
        $article_to_check = Article::with(['images', 'category', 'user'])->whereNull('is_accepted')->oldest()->first();
        return view('revisor.index', compact('article_to_check'));
    }

    public function accept(Article $article)
    {
        $article->setAccepted(true);
        return redirect()
        ->back()
        ->with('message', __('ui.accepted_article', ['title' => $article->title]));
    }

    public function reject(Article $article)
    {
        $article->setAccepted(false);
        return redirect()
        ->back()
        ->with('message', __('ui.rejected_article', ['title' => $article->title]));
    }

    public function becomeRevisor()
    {
        Mail::to('admin@presto.it')->send(new BecomeRevisor(Auth::user()));
        return redirect()->route('homepage')->with('message', __('ui.revisor_request_success'));
    }

    public function makeRevisor(User $user)
    {
        // La rotta è protetta dal middleware 'signed': il link è valido solo se generato dalla mail
        $user->is_revisor = true;
        $user->save();
        return redirect()->route('homepage')->with('message', __('ui.revisor_promoted', ['name' => $user->name]));
    }
}
