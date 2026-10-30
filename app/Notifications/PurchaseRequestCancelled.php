<?php

namespace App\Notifications;

use App\Models\PurchaseRequest;
use App\Support\MailText;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

/**
 * Email al venditore: l'acquirente ha annullato la richiesta, gli oggetti sono di nuovo disponibili.
 */
class PurchaseRequestCancelled extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public PurchaseRequest $purchaseRequest)
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
        $request = $this->purchaseRequest->loadMissing(['buyer', 'items']);

        return (new MailMessage)
            ->subject(__('ui.cancel_mail_subject', ['name' => $request->buyer->name]))
            ->greeting(__('ui.review_mail_greeting', ['name' => $notifiable->name]))
            ->line(__('ui.cancel_mail_line', ['name' => MailText::plain($request->buyer->name), 'items' => MailText::plain($request->items->pluck('title')->join(', '))]))
            ->action(__('ui.sales_title'), route('sales.index'));
    }
}
