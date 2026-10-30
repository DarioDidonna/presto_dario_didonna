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
 * Email all'acquirente: il venditore ha accettato o rifiutato la richiesta d'acquisto.
 */
class PurchaseRequestAnswered extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public PurchaseRequest $purchaseRequest, public bool $accepted)
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
        $request = $this->purchaseRequest->loadMissing(['seller', 'items']);
        $status = $this->accepted ? 'accepted' : 'declined';

        $mail = (new MailMessage)
            ->subject(__("ui.answer_mail_{$status}_subject", ['name' => $request->seller->name]))
            ->greeting(__('ui.review_mail_greeting', ['name' => $notifiable->name]))
            ->line(__("ui.answer_mail_{$status}_line", ['name' => MailText::plain($request->seller->name)]));

        foreach ($request->items as $item) {
            $mail->line('• '.MailText::plain($item->title).' — '.number_format((float) $item->price, 2, ',', '.').' €');
        }

        if ($this->accepted) {
            // Ora che ha accettato, il contatto del venditore serve per accordarsi su pagamento e consegna
            $mail->line(__('ui.answer_mail_contact', ['email' => MailText::plain($request->seller->email)]));
        }

        return $mail->action(__('ui.orders_title'), route('orders.index'));
    }
}
