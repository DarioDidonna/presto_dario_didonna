# Presto.it — agente AI

Servizio Python che affianca l'applicazione Laravel: un agente **LangChain/LangGraph** con un modello **locale** servito da [Ollama](https://ollama.com) (nessuna chiave API, funziona offline).

- **FastAPI** espone l'agente via HTTP; lo chiama solo Laravel.
- I *tool* dell'agente usano **API interne di Laravel in sola lettura**: il servizio non accede mai al database.

## Requisiti

- [uv](https://docs.astral.sh/uv/) (`brew install uv`): installa da solo Python 3.12 e le dipendenze
- Ollama con il modello scaricato: `ollama pull qwen2.5:3b`

## Avvio

```bash
cd ai-agent
cp .env.example .env
uv sync
uv run presto-ai-agent        # http://127.0.0.1:8001
curl http://127.0.0.1:8001/health
```

## Modello

`qwen2.5:3b` (~2,2 GB in memoria) è stato scelto confrontandolo con `qwen3:4b` sul tool calling in italiano, su un Mac M3 con 8 GB:
stessa accuratezza, ma 1–2 secondi per risposta contro 17–73 (il modello più grande manda il sistema in swap insieme al resto della demo).
Si cambia con `PRESTO_AI_MODEL` nel `.env`.

## Test e stile

```bash
uv run pytest
uv run ruff check . && uv run ruff format --check .
```
