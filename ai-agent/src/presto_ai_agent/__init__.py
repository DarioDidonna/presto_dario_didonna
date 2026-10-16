def main() -> None:
    """Avvia il servizio: `uv run presto-ai-agent`."""
    import uvicorn

    from .config import get_settings

    settings = get_settings()
    uvicorn.run("presto_ai_agent.app:app", host=settings.host, port=settings.port)
