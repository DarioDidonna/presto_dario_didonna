<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Nuovo messaggio in una conversazione, inviato subito (non in coda) all'altra persona tramite Reverb.
 * Trasmette solo l'id: il testo lo rilegge il componente Livewire, con i suoi controlli di accesso.
 */
class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(public Message $message) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('conversations.'.$this->message->purchase_request_id);
    }

    public function broadcastWith(): array
    {
        return ['id' => $this->message->id];
    }
}
