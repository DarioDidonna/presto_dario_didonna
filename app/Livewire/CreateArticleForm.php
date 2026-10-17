<?php

namespace App\Livewire;

use App\Livewire\Concerns\HandlesArticleForm;
use App\Models\Article;
use App\Models\Category;
use App\Services\AgentUnavailableException;
use App\Services\AiAgentClient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateArticleForm extends Component
{
    use HandlesArticleForm, WithFileUploads;

    public const MAX_SUGGESTIONS_PER_MINUTE = 5;

    public $article;

    // Categoria proposta dall'AI e non ancora confermata: il modello piccolo sbaglia circa 3 volte su 8
    public bool $categoryToCheck = false;

    // Esito dell'ultima richiesta di bozza: 'filled', 'kept' (campi già scritti, lasciati com'erano) o null
    public ?string $suggestion = null;

    /**
     * "Compila con l'AI": bozza dalla prima foto. Riempie solo i campi ancora vuoti (mai il prezzo) e segna la
     * categoria come da controllare. L'utente rivede e modifica tutto prima di pubblicare.
     */
    public function suggestFromPhoto(AiAgentClient $agent): void
    {
        $this->resetErrorBag('ai');
        $this->suggestion = null;
        if (! $agent->isEnabled() || empty($this->images)) {
            return;
        }

        $key = 'describe:'.Auth::id();
        if (RateLimiter::tooManyAttempts($key, self::MAX_SUGGESTIONS_PER_MINUTE)) {
            $this->addError('ai', __('ui.ai_draft_too_many', ['seconds' => RateLimiter::availableIn($key)]));

            return;
        }
        RateLimiter::hit($key);

        $categories = Category::orderBy('name')->pluck('id', 'name');

        try {
            $draft = $agent->describe($this->images[0]->getRealPath(), $categories->keys()->all(), app()->getLocale());
        } catch (AgentUnavailableException $error) {
            report($error);
            $this->addError('ai', __('ui.ai_draft_unavailable'));

            return;
        }

        if (! $draft['found']) {
            $this->addError('ai', __('ui.ai_draft_not_found'));

            return;
        }

        $filled = false;
        foreach (['title', 'description'] as $field) {
            if (blank($this->form->{$field}) && $draft[$field] !== '') {
                $this->form->{$field} = $draft[$field];
                $filled = true;
            }
        }
        if (blank($this->form->category) && $draft['category'] !== null) {
            $this->form->category = $categories[$draft['category']];
            $this->categoryToCheck = $filled = true;
        }

        $this->suggestion = $filled ? 'filled' : 'kept';
    }

    // Scegliere la categoria a mano la conferma
    public function updatedFormCategory(): void
    {
        $this->categoryToCheck = false;
    }

    public function store()
    {
        $this->validateArticle();

        $this->article = Article::create([...$this->form->attributes(), 'user_id' => Auth::id()]);

        $this->storeNewImages($this->article);

        session()->flash('message', __('ui.article_published'));
        $this->form->reset();
        $this->reset(['images', 'temporary_images', 'categoryToCheck', 'suggestion']);
    }

    public function render()
    {
        return view('livewire.article-form', [
            'submitAction' => 'store',
            'submitLabel' => __('ui.publish_ad'),
            'submitIcon' => 'bi-plus-circle',
            'existingImages' => collect(),
            'aiDraft' => app(AiAgentClient::class)->isEnabled(),
        ]);
    }
}
