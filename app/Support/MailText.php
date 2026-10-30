<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Testo scritto dagli utenti dentro le email: le notifiche di Laravel sono in Markdown, quindi senza questa pulizia
 * "[Paga qui](https://...)" diventerebbe un vero link spedito dal sito (phishing). I caratteri < e > li neutralizza già
 * l'escape di Blade.
 */
class MailText
{
    public static function plain(?string $text): string
    {
        return (string) preg_replace('/([\\\\`*_\[\]~|#])/', '\\\\$1', Str::squish((string) $text));
    }
}
