<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\ExecutableFinder;

#[Signature('app:ai-setup {--env= : Percorso del .env di Laravel} {--agent-env= : Percorso di ai-agent/.env}')]
#[Description('Configura l\'agente AI: token condiviso tra Laravel e ai-agent/, controllo di uv, Ollama e modello')]
class AiSetup extends Command
{
    public function handle()
    {
        if ($this->laravel->environment('production')) {
            $this->error('Comando non disponibile in produzione: imposta le variabili a mano.');

            return self::FAILURE;
        }

        $laravelEnv = $this->option('env') ?: base_path('.env');
        $agentEnv = $this->option('agent-env') ?: base_path('ai-agent/.env');

        if (! is_file($laravelEnv)) {
            $this->error("File non trovato: {$laravelEnv} (copia prima .env.example in .env)");

            return self::FAILURE;
        }
        if (! is_file($agentEnv)) {
            copy(base_path('ai-agent/.env.example'), $agentEnv);
        }

        // Riusa il token esistente: cambiarlo a ogni esecuzione romperebbe un agente già avviato
        $token = $this->readValue($laravelEnv, 'AI_AGENT_TOKEN') ?: bin2hex(random_bytes(32));

        $this->writeValue($laravelEnv, 'AI_AGENT_TOKEN', $token);
        $this->writeValue($laravelEnv, 'AI_AGENT_URL', $this->readValue($laravelEnv, 'AI_AGENT_URL') ?: 'http://127.0.0.1:8001');
        $this->writeValue($agentEnv, 'PRESTO_AI_API_TOKEN', $token);
        $this->writeValue($agentEnv, 'PRESTO_AI_LARAVEL_URL', rtrim((string) config('app.url'), '/'));

        $this->components->info('Token condiviso configurato in .env e ai-agent/.env.');
        $this->checks($this->readValue($agentEnv, 'PRESTO_AI_MODEL') ?: 'qwen2.5:3b');
        $this->callSilently('config:clear');

        return self::SUCCESS;
    }

    private function checks(string $model): void
    {
        $this->components->twoColumnDetail('uv', (new ExecutableFinder)->find('uv') ? '<fg=green>installato</>' : '<fg=yellow>mancante: brew install uv</>');

        try {
            $models = collect(Http::timeout(2)->get('http://localhost:11434/api/tags')->json('models', []))->pluck('name');
            $this->components->twoColumnDetail('Ollama', '<fg=green>attivo</>');
            $this->components->twoColumnDetail("Modello {$model}", $models->contains($model)
                ? '<fg=green>scaricato</>'
                : "<fg=yellow>mancante: ollama pull {$model}</>");
        } catch (\Throwable) {
            $this->components->twoColumnDetail('Ollama', '<fg=yellow>non raggiungibile: installa e avvia Ollama</>');
        }
    }

    private function readValue(string $file, string $key): ?string
    {
        preg_match('/^'.preg_quote($key, '/').'=(.*)$/m', (string) file_get_contents($file), $match);

        return isset($match[1]) && trim($match[1], " \"'") !== '' ? trim($match[1], " \"'") : null;
    }

    private function writeValue(string $file, string $key, string $value): void
    {
        $content = (string) file_get_contents($file);
        $line = "{$key}={$value}";
        $pattern = '/^'.preg_quote($key, '/').'=.*$/m';

        $content = preg_match($pattern, $content)
            ? preg_replace($pattern, $line, $content)
            : rtrim($content)."\n{$line}\n";

        file_put_contents($file, $content);
    }
}
