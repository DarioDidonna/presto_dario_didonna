<?php

namespace App\View\Composers;

use Illuminate\Support\Facades\App;

/**
 * Lingua delle pagine di errore, impostata prima che la vista (e il suo contenuto) venga disegnata.
 * Su un URL inesistente la sessione non è avviata e il middleware della lingua non gira:
 * si usa la lingua preferita del browser tra quelle del sito, altrimenti l'italiano.
 */
class ErrorPageLocaleComposer
{
    public function compose(): void
    {
        $available = config('app.available_locales', ['it']);
        $request = request();

        $locale = $request->hasSession()
            ? $request->session()->get('locale', 'it')
            : $request->getPreferredLanguage(array_merge(['it'], $available));

        App::setLocale(in_array($locale, $available, true) ? $locale : 'it');
    }
}
