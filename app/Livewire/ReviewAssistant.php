<?php

namespace App\Livewire;

use App\Models\Article;
use App\Services\AgentUnavailableException;
use App\Services\AiAgentClient;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Parere dell'AI nella dashboard del revisore: solo un suggerimento, non cambia mai lo stato dell'annuncio.
 */
class ReviewAssistant extends Component
{
    #[Locked]
    public int $articleId;

    #[Locked]
    public ?array $suggestion = null;

    #[Locked]
    public bool $unavailable = false;

    public function mount(int $articleId): void
    {
        $this->authorizeRevisor();
        $this->articleId = $articleId;
        // Parere già chiesto per questa versione dell'annuncio: si mostra subito
        $this->suggestion = Cache::get($this->cacheKey());
    }

    public function ask(AiAgentClient $agent): void
    {
        $this->authorizeRevisor();
        $this->unavailable = false;

        try {
            $this->suggestion = Cache::remember(
                $this->cacheKey(),
                now()->addDay(),
                fn () => $agent->review($this->article(), app()->getLocale()),
            );
        } catch (AgentUnavailableException $error) {
            report($error);
            $this->unavailable = true;
        }
    }

    private function article(): Article
    {
        return Article::findOrFail($this->articleId);
    }

    // Una versione per lingua e per modifica: se l'autore cambia l'annuncio il parere si ricalcola
    private function cacheKey(): string
    {
        return sprintf('review-suggestion:%d:%d:%s', $this->articleId, $this->article()->updated_at?->getTimestamp(), app()->getLocale());
    }

    private function authorizeRevisor(): void
    {
        abort_unless(auth()->user()?->is_revisor, 403);
    }

    public function render()
    {
        return view('livewire.review-assistant');
    }
}
