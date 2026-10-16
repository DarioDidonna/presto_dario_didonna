<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

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
            $response = Http::baseUrl(config('services.ai_agent.url'))
                ->withToken(config('services.ai_agent.token'))
                ->acceptJson()
                // Un modello locale piccolo risponde in pochi secondi, ma il primo messaggio deve caricarlo in memoria
                ->timeout(60)
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
}
