<?php

namespace App\Livewire;

use App\Events\MessageSent;
use App\Models\PurchaseRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * Messaggi tra acquirente e venditore di una richiesta d'acquisto.
 * Ogni azione ricontrolla i permessi: l'id della richiesta è bloccato, ma lo stato può cambiare nel frattempo.
 */
class Conversation extends Component
{
    public const MAX_MESSAGES_PER_MINUTE = 10;

    #[Locked]
    public int $purchaseRequestId;

    #[Validate('required|string|max:1000')]
    public string $body = '';

    public function mount(PurchaseRequest $purchaseRequest): void
    {
        Gate::authorize('view', $purchaseRequest);
        $this->purchaseRequestId = $purchaseRequest->id;
        $this->markAsRead($purchaseRequest);
    }

    public function send(): void
    {
        $request = $this->purchaseRequest();
        Gate::authorize('message', $request);

        $this->body = trim($this->body);
        $this->validate();

        $key = 'conversation:'.auth()->id();
        if (RateLimiter::tooManyAttempts($key, self::MAX_MESSAGES_PER_MINUTE)) {
            $this->addError('body', __('ui.messages_too_many', ['seconds' => RateLimiter::availableIn($key)]));

            return;
        }
        RateLimiter::hit($key);

        $message = $request->messages()->create(['user_id' => auth()->id(), 'body' => $this->body]);
        $this->reset('body');
        $this->dispatch('conversation-updated');

        // Se Reverb è spento il messaggio è comunque salvato: l'altra persona lo vedrà ricaricando la pagina.
        // Niente arrow function: broadcast() invia alla distruzione dell'oggetto, che deve avvenire dentro rescue()
        rescue(function () use ($message) {
            broadcast(new MessageSent($message))->toOthers();
        });
    }

    // Messaggio arrivato dall'altra persona: il componente si ridisegna da solo, qui resta da segnarlo come letto
    #[On('echo-private:conversations.{purchaseRequestId},MessageSent')]
    public function messageReceived(): void
    {
        $this->markAsRead($this->purchaseRequest());
        $this->dispatch('conversation-updated');
    }

    public function render()
    {
        $request = $this->purchaseRequest();

        return view('livewire.conversation', [
            'messages' => $request->messages()->with('user')->get(),
            'canWrite' => Gate::allows('message', $request),
            'status' => $request->status,
        ]);
    }

    private function purchaseRequest(): PurchaseRequest
    {
        $request = PurchaseRequest::findOrFail($this->purchaseRequestId);
        Gate::authorize('view', $request);

        return $request;
    }

    // All'apertura i messaggi dell'altra persona diventano letti
    private function markAsRead(PurchaseRequest $request): void
    {
        $request->messages()->where('user_id', '!=', auth()->id())->whereNull('read_at')->update(['read_at' => now()]);
    }
}
