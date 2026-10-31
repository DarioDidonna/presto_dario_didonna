<?php

namespace App\Notifications\Concerns;

/**
 * Le email in coda si ritentano: se il server di posta rifiuta un invio (limite al secondo, servizio giù) non vanno perse.
 */
trait RetriesDelivery
{
    public int $tries = 4;

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [15, 60, 300];
    }
}
