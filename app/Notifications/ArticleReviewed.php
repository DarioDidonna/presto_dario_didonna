<?php

namespace App\Notifications;

use App\Models\Article;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

/**
 * Email all'autore con l'esito della revisione. Inviata in coda (il revisore non aspetta il server di posta)
 * e nella lingua dell'autore (User::preferredLocale()).
 */
class ArticleReviewed extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Article $article, public bool $accepted)
    {
        // Parte solo dopo il commit: il job trova l'annuncio con lo stato già aggiornato
        $this->afterCommit();
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
        $status = $this->accepted ? 'accepted' : 'rejected';
        $mail = (new MailMessage)
            ->subject(__("ui.review_mail_{$status}_subject", ['title' => $this->article->title]))
            ->greeting(__('ui.review_mail_greeting', ['name' => $notifiable->name]))
            ->line(__("ui.review_mail_{$status}_line", ['title' => $this->article->title]));

        if ($this->accepted) {
            return $mail->action(__('ui.review_mail_view_ad'), route('article.show', $this->article));
        }

        // Rifiutato: l'autore può correggerlo, e la modifica lo rimette in revisione
        return $mail
            ->line(__('ui.review_mail_rejected_hint'))
            ->action(__('ui.edit_ad'), route('article.edit', $this->article));
    }
}
