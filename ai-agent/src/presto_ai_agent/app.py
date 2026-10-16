"""Servizio HTTP dell'agente. Laravel lo chiama; l'agente non accede mai direttamente al database."""

from typing import Annotated

import httpx
from fastapi import Depends, FastAPI

from .config import Settings, get_settings

app = FastAPI(title="Presto.it AI agent", version="0.1.0")


@app.get("/health")
def health(settings: Annotated[Settings, Depends(get_settings)]) -> dict:
    """Stato del servizio: Ollama raggiungibile e modello configurato già scaricato."""
    try:
        response = httpx.get(f"{settings.ollama_base_url}/api/tags", timeout=2)
        response.raise_for_status()
        models = [model["name"] for model in response.json().get("models", [])]
    except httpx.HTTPError:
        return {"status": "degraded", "ollama": False, "model": settings.model, "model_available": False}

    available = settings.model in models
    return {
        "status": "ok" if available else "degraded",
        "ollama": True,
        "model": settings.model,
        "model_available": available,
    }
