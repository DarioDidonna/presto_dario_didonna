<?php

namespace App\Notifications;

use App\Models\Review;
use App\Support\MailText;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

// Email al venditore quando un acquirente lo recensisce
class ReviewReceived extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public bool $deleteWhenMissingModels = true;

    public function __construct(public Review $review)
    {
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
        // La recensione viene cancellata insieme al suo autore (chiave esterna): l'autore c'è sempre
        $reviewer = $this->review->reviewer->name;
        $mail = (new MailMessage)
            ->subject(__('ui.reviews_mail_subject', ['name' => $reviewer]))
            ->greeting(__('ui.review_mail_greeting', ['name' => $notifiable->name]))
            ->line(__('ui.reviews_mail_line', ['name' => MailText::plain($reviewer), 'rating' => $this->review->rating]));

        if ($this->review->comment) {
            $mail->line('«'.MailText::plain(Str::limit($this->review->comment, 300)).'»');
        }

        return $mail->action(__('ui.sales_title'), route('sales.index'));
    }
}
