<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

/**
 * Email per un messaggio rimasto non letto. Parte con un ritardo (Conversation::EMAIL_DELAY_MINUTES):
 * chi sta già chattando non riceve un'email per ogni messaggio.
 */
class NewMessage extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public bool $deleteWhenMissingModels = true;

    public function __construct(public Message $message)
    {
        $this->afterCommit();
    }

    /**
     * Al momento dell'invio: niente email se il messaggio è stato letto, o se c'è un messaggio più vecchio
     * dello stesso mittente ancora non letto (per quello l'email è già partita: una sola per ogni raffica).
     */
    public function shouldSend(object $notifiable, string $channel): bool
    {
        $message = $this->message->fresh();

        return $message !== null
            && $message->read_at === null
            && ! Message::where('purchase_request_id', $message->purchase_request_id)
                ->where('user_id', $message->user_id)
                ->whereNull('read_at')
                ->where('id', '<', $message->id)
                ->exists();
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $sender = $this->message->user->name;

        return (new MailMessage)
            ->subject(__('ui.messages_mail_subject', ['name' => $sender]))
            ->greeting(__('ui.review_mail_greeting', ['name' => $notifiable->name]))
            ->line(__('ui.messages_mail_line', ['name' => $sender]))
            ->line('«'.Str::limit($this->message->body, 300).'»')
            ->action(__('ui.messages_mail_action'), route('conversations.show', $this->message->purchase_request_id));
    }
}
