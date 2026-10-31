<?php

namespace App\Notifications;

use App\Notifications\Concerns\RetriesDelivery;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

// In coda: se il server di posta rifiuta l'invio, chi si registra non deve vedere una pagina di errore
class QueuedVerifyEmail extends VerifyEmail implements ShouldQueue
{
    use Queueable, RetriesDelivery;
}
