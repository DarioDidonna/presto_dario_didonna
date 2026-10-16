#!/usr/bin/env bash
# Avvia l'agente AI (ai-agent/) per "composer run dev".
# composer run dev usa --kill-others: se questo processo terminasse spegnerebbe anche il sito,
# quindi in caso di problemi si avvisa e si resta in attesa invece di uscire.

cd "$(dirname "$0")/../ai-agent" || exit 1

wait_forever() {
    echo "[agente] $1"
    echo "[agente] Il sito funziona comunque: l'assistente risponderà \"non disponibile\"."
    exec tail -f /dev/null
}

command -v uv >/dev/null 2>&1 || wait_forever "uv non è installato (brew install uv): assistente AI disattivato."

[ -f .env ] || echo "[agente] Manca ai-agent/.env: esegui \"php artisan app:ai-setup\" nella cartella del progetto."

curl -s --max-time 2 http://localhost:11434/api/tags >/dev/null \
    || echo "[agente] Ollama non risponde su localhost:11434: avvialo (app Ollama o \"ollama serve\")."

uv run presto-ai-agent || wait_forever "l'agente si è fermato per un errore (vedi sopra)."
