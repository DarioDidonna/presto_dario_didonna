<?php

namespace App\Notifications\Concerns;

use DateTimeInterface;
use Illuminate\Queue\Middleware\RateLimited;

/**
 * Consegna delle email in coda: una alla volta (limite "mail-delivery") e, se il server di posta le rifiuta, di nuovo
 * più tardi invece di andare perse.
 *
 * Una scadenza al posto di un numero di tentativi: quando il limite rimanda un'email, Laravel lo conta come tentativo,
 * e con molte email in attesa li avrebbe esauriti senza nemmeno provare a spedirle. Contano solo gli errori veri.
 */
trait RetriesDelivery
{
    public int $maxExceptions = 4;

    public function retryUntil(): DateTimeInterface
    {
        return now()->addHours(2);
    }

    /**
     * Attese diverse per ogni email: con le stesse, quelle rifiutate insieme ripartivano insieme e si scontravano di nuovo.
     *
     * @return list<int>
     */
    public function backoff(): array
    {
        return [random_int(10, 30), random_int(45, 90), random_int(200, 400)];
    }

    /**
     * @return list<object>
     */
    public function middleware(): array
    {
        // Con la coda "sync" un'email rimandata andrebbe persa: non c'è nessuno che la riprenda
        return config('queue.default') === 'sync' ? [] : [new RateLimited('mail-delivery')];
    }
}
