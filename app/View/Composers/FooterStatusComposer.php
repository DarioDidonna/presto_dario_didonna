<?php

namespace App\View\Composers;

use App\Services\AiAgentClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * "Stato dei servizi" nel footer: dati veri, controllati al massimo una volta al minuto (Cache) e con tempi
 * massimi di un secondo, così il footer non rallenta mai la pagina anche se un servizio è spento.
 */
class FooterStatusComposer
{
    public const CACHE_KEY = 'footer-services';

    public function __construct(private AiAgentClient $agent) {}

    public function compose(View $view): void
    {
        $view->with('services', Cache::remember(self::CACHE_KEY, 60, fn () => $this->services()));
    }

    /**
     * @return list<array{key: string, state: string, value: string}>
     */
    private function services(): array
    {
        $health = $this->agent->isEnabled() ? $this->agent->health() : null;
        $agent = match (true) {
            ! $this->agent->isEnabled() => 'disabled',
            $health === null => 'off',
            default => 'on',
        };
        $modelReady = $health !== null && $health['model_available'];
        $revisors = $this->revisorsOnline();
        $realtime = $this->realtime();

        return [
            [
                'key' => 'revisors', 'state' => $revisors > 0 ? 'on' : 'idle',
                'value' => trans_choice('ui.footer_value_online', $revisors, ['count' => $revisors]),
            ],
            // Il rilevatore dei volti sta nel servizio Python ma non usa Ollama: basta che il servizio risponda
            ['key' => 'faces', 'state' => $agent, 'value' => __('ui.footer_value_'.$agent)],
            [
                'key' => 'assistant', // Servizio acceso ma modello della chat non scaricato: l'assistente non può rispondere
                'state' => $agent === 'on' && ! $modelReady ? 'off' : $agent,
                'value' => $modelReady ? 'qwen2.5:3b' : __('ui.footer_value_'.($agent === 'on' ? 'off' : $agent)),
            ],
            ['key' => 'realtime', 'state' => $realtime, 'value' => $realtime === 'on' ? 'Reverb' : __('ui.footer_value_'.$realtime)],
        ];
    }

    // Revisori con una sessione attiva negli ultimi 5 minuti (con le sessioni su database, come in sviluppo)
    private function revisorsOnline(): int
    {
        if (config('session.driver') !== 'database') {
            return 0;
        }

        return DB::table(config('session.table', 'sessions'))
            ->join('users', 'users.id', '=', 'sessions.user_id')
            ->where('users.is_revisor', true)
            ->where('sessions.last_activity', '>=', now()->subMinutes(5)->getTimestamp())
            ->distinct()
            ->count('sessions.user_id');
    }

    // Reverb in ascolto sulla sua porta (stessi host e porta usati da Laravel per trasmettere)
    private function realtime(): string
    {
        if (config('broadcasting.default') !== 'reverb') {
            return 'disabled';
        }

        $options = config('broadcasting.connections.reverb.options');
        $socket = @fsockopen((string) ($options['host'] ?? '127.0.0.1'), (int) ($options['port'] ?? 8080), $errno, $error, 0.5);
        if ($socket === false) {
            return 'off';
        }
        fclose($socket);

        return 'on';
    }
}
