<?php

namespace App\Notifications;

use App\Models\PurchaseRequest;
use App\Notifications\Concerns\RetriesDelivery;
use App\Support\MailText;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

/**
 * Email al venditore: un utente vuole acquistare alcuni suoi oggetti.
 */
class PurchaseRequested extends Notification implements ShouldQueue
{
    use Queueable, RetriesDelivery, SerializesModels;

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
        $buyer = $request->buyer;

        $mail = (new MailMessage)
            ->subject(__('ui.purchase_mail_subject', ['name' => $buyer->name]))
            ->greeting(__('ui.review_mail_greeting', ['name' => $notifiable->name]))
            ->line(__('ui.purchase_mail_intro', ['name' => MailText::plain($buyer->name)]));

        foreach ($request->items as $item) {
            $mail->line('• '.MailText::plain($item->title).' — '.number_format((float) $item->price, 2, ',', '.').' €');
        }

        $mail->line(__('ui.purchase_mail_total', ['total' => number_format((float) $request->total, 2, ',', '.')]));

        if ($request->message) {
            $mail->line(__('ui.purchase_mail_message').' «'.MailText::plain($request->message).'»');
        }

        // L'email dell'acquirente serve al venditore per accordarsi su pagamento e consegna
        return $mail
            ->line(__('ui.purchase_mail_contact', ['email' => MailText::plain($buyer->email)]))
            ->action(__('ui.purchase_mail_action'), route('sales.index'))
            ->line(__('ui.purchase_mail_reserved'));
    }
}
