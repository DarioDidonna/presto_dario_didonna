<?php

namespace App\Notifications;

use App\Notifications\Concerns\RetriesDelivery;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

// In coda: se il server di posta rifiuta l'invio, chi chiede il reset non deve vedere una pagina di errore
class QueuedResetPassword extends ResetPassword implements ShouldQueue
{
    use Queueable, RetriesDelivery;
}
