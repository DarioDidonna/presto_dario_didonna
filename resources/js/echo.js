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

// Messaggio arrivato mentre si è su un'altra pagina: aggiorna i contatori della navbar
const nav = document.querySelector('[data-user-id]');
if (window.Echo && nav) {
    window.Echo.private(`App.Models.User.${nav.dataset.userId}`).listen('MessageSent', (event) => {
        // Chat di quella richiesta già aperta: il messaggio viene letto subito
        if (document.querySelector(`[data-conversation-id="${event.purchase_request_id}"]`)) return;

        const badge = document.querySelector(`[data-unread-badge="${event.box}"]`);
        if (badge) {
            const count = badge.querySelector('[data-unread-count]');
            count.textContent = (parseInt(count.textContent, 10) || 0) + 1;
            badge.hidden = false;
        }
        document.querySelector('[data-attention-dot]')?.removeAttribute('hidden');
    });
}
