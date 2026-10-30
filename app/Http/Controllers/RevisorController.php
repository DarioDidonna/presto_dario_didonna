<?php

namespace App\Http\Controllers;

use App\Mail\BecomeRevisor;
use App\Models\Article;
use App\Models\User;
use App\Notifications\ArticleReviewed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class RevisorController extends Controller
{
    // Finestra per annullare l'ultima decisione; l'email all'autore parte solo dopo questo tempo
    public const UNDO_MINUTES = 5;

    public function index()
    {
        $article_to_check = Article::with(['images', 'category', 'user'])->whereNull('is_accepted')->oldest()->first();

        return view('revisor.index', compact('article_to_check'));
    }

    public function accept(Article $article)
    {
        $this->review($article, true);

        return redirect()
            ->back()
            ->with('message', __('ui.accepted_article', ['title' => $article->title]));
    }

    public function reject(Article $article)
    {
        $this->review($article, false);

        return redirect()
            ->back()
            ->with('message', __('ui.rejected_article', ['title' => $article->title]));
    }

    // Aggiorna lo stato e avvisa l'autore, solo se l'esito cambia davvero (niente email doppie)
    private function review(Article $article, bool $accepted): void
    {
        if ($article->is_accepted === $accepted) {
            return;
        }

        $article->setAccepted($accepted);

        // L'email parte dopo la finestra di annullamento e solo se la decisione è ancora valida (ArticleReviewed::shouldSend)
        $article->user?->notify(
            (new ArticleReviewed($article, $accepted))->delay(now()->addMinutes(self::UNDO_MINUTES))
        );

        session()->put('last_review', [
            'article_id' => $article->id,
            'accepted' => $accepted,
            'decided_at' => $article->updated_at->getTimestamp(),
        ]);
    }

    public function undo()
    {
        $review = session()->pull('last_review');
        $article = $review ? Article::find($review['article_id']) : null;

        // Annullabile solo la propria ultima decisione, entro la finestra e se nessuno l'ha cambiata nel frattempo
        $valid = $article
            && $article->is_accepted === $review['accepted']
            && $article->updated_at->getTimestamp() === $review['decided_at']
            && now()->getTimestamp() - $review['decided_at'] <= self::UNDO_MINUTES * 60;

        if (! $valid) {
            return redirect()->route('revisor.index')->with('errorMessage', __('ui.undo_expired'));
        }

        $article->setAccepted(null);

        return redirect()->route('revisor.index')->with('message', __('ui.undo_done', ['title' => $article->title]));
    }

    public function becomeRevisor()
    {
        Mail::to(config('app.admin_email'))->send(new BecomeRevisor(Auth::user()));

        return redirect()->route('homepage')->with('message', __('ui.revisor_request_success'));
    }

    // Solo una pagina di conferma: gli antivirus di posta aprono i link da soli, una GET non deve promuovere nessuno
    public function confirmRevisor(User $user)
    {
        return view('revisor.confirm', compact('user'));
    }

    public function makeRevisor(Request $request, User $user)
    {
        // La rotta è protetta dal middleware 'signed': il link è valido solo se generato dalla mail. Ed è monouso
        abort_unless(Cache::add('revisor-link:'.$request->query('signature'), true, now()->addDays(BecomeRevisor::LINK_DAYS)), 403);

        $user->is_revisor = true;
        $user->save();

        return redirect()->route('homepage')->with('message', __('ui.revisor_promoted', ['name' => $user->name]));
    }
}
