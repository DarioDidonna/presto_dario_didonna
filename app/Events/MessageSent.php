<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Nuovo messaggio in una conversazione, inviato subito (non in coda) tramite Reverb su due canali privati:
 * quello della conversazione (la chat aperta si aggiorna) e quello personale del destinatario (contatore nella navbar).
 * Niente testo: lo rilegge il componente Livewire, con i suoi controlli di accesso.
 */
class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(public Message $message) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        $request = $this->message->purchaseRequest;

        return [
            new PrivateChannel('conversations.'.$request->id),
            new PrivateChannel('App.Models.User.'.$request->counterpart($this->message->user)?->id),
        ];
    }

    public function broadcastWith(): array
    {
        $request = $this->message->purchaseRequest;

        return [
            'id' => $this->message->id,
            'purchase_request_id' => $request->id,
            // Voce del menu da aggiornare per il destinatario: "I miei ordini" o "Richieste ricevute"
            'box' => $request->buyer_id === $this->message->user_id ? 'sales' : 'orders',
        ];
    }
}
