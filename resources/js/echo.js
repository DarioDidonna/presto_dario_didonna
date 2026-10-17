import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Messaggi in tempo reale tramite Laravel Reverb. Senza configurazione (per esempio nella build della CI)
// Echo non parte: il sito funziona lo stesso e i messaggi nuovi compaiono ricaricando la pagina.
if (import.meta.env.VITE_REVERB_APP_KEY) {
    window.Pusher = Pusher;

    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
        wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
    });
}
