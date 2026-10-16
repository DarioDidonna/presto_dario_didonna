<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Image;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Client del servizio Python dell'agente (ai-agent/). Il browser non lo chiama mai direttamente: passa da qui.
 */
class AiAgentClient
{
    public function isEnabled(): bool
    {
        return (string) config('services.ai_agent.token') !== '';
    }

    /**
     * @return array{reply: string, conversation_id: string, article_ids: list<int>}
     *
     * @throws AgentUnavailableException agente spento, Ollama spento o risposta non valida
     */
    public function chat(string $message, ?string $conversationId, string $lang): array
    {
        try {
            $response = $this->http()
                ->post('/chat', array_filter([
                    'message' => $message,
                    'conversation_id' => $conversationId,
                    'lang' => $lang,
                ]))
                ->throw()
                ->json();
        } catch (ConnectionException|RequestException $error) {
            throw new AgentUnavailableException('Assistente non disponibile: '.$error->getMessage(), previous: $error);
        }

        if (! is_array($response) || ! is_string($response['reply'] ?? null) || ! is_string($response['conversation_id'] ?? null)) {
            throw new AgentUnavailableException('Risposta non valida dall\'assistente.');
        }

        return [
            'reply' => $response['reply'],
            'conversation_id' => $response['conversation_id'],
            // Solo gli ID: dati, link e foto degli annunci li ricostruisce Laravel dal proprio database
            'article_ids' => collect($response['articles'] ?? [])->pluck('id')->filter(fn ($id) => is_int($id))->values()->all(),
        ];
    }

    /**
     * Parere sull'annuncio per il revisore (solo un suggerimento: decide sempre il revisore).
     *
     * @return array{recommendation: string, confidence: float, reasons: list<string>, flags: list<array{code: string, blocking: bool}>, model_used: bool}
     *
     * @throws AgentUnavailableException
     */
    public function review(Article $article, string $lang): array
    {
        $article->loadMissing(['images', 'category']);
        $levels = ['unknown' => 0, 'unlikely' => 1, 'possible' => 2, 'likely' => 3];

        // Per ogni categoria SafeSearch, la valutazione peggiore tra le foto dell'annuncio
        $safeSearch = [];
        foreach (['adult', 'violence', 'racy', 'medical', 'spoof'] as $category) {
            $worst = $article->images
                ->map(fn ($image) => Image::likelihoodLevel($image->{$category}))
                ->sortByDesc(fn ($level) => $levels[$level])
                ->first();
            if ($worst !== null) {
                $safeSearch[$category] = $worst;
            }
        }

        try {
            $response = $this->http()
                ->post('/review', [
                    'title' => Str::limit($article->title, 200, ''),
                    'description' => Str::limit($article->description, 5000, ''),
                    'price' => (float) $article->price,
                    'category' => $article->category?->name,
                    'photos' => min($article->images->count(), 20),
                    'labels' => $article->images->pluck('labels')->flatten()->filter()->unique()->take(60)->values()->all(),
                    'safe_search' => $safeSearch,
                    'lang' => $lang,
                ])
                ->throw()
                ->json();
        } catch (ConnectionException|RequestException $error) {
            throw new AgentUnavailableException('Assistente non disponibile: '.$error->getMessage(), previous: $error);
        }

        if (! is_array($response) || ! in_array($response['recommendation'] ?? null, ['accept', 'reject', 'unsure'], true)) {
            throw new AgentUnavailableException('Risposta non valida dall\'assistente.');
        }

        return [
            'recommendation' => $response['recommendation'],
            'confidence' => (float) ($response['confidence'] ?? 0),
            'reasons' => array_values(array_filter($response['reasons'] ?? [], 'is_string')),
            'flags' => array_values(array_filter($response['flags'] ?? [], fn ($flag) => is_array($flag) && is_string($flag['code'] ?? null))),
            'model_used' => (bool) ($response['model_used'] ?? false),
        ];
    }

    private function http(): PendingRequest
    {
        return Http::baseUrl(config('services.ai_agent.url'))
            ->withToken(config('services.ai_agent.token'))
            ->acceptJson()
            // Un modello locale piccolo risponde in pochi secondi, ma la prima richiesta deve caricarlo in memoria
            ->timeout(60);
    }
}
