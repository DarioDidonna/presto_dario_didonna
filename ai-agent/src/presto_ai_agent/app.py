"""Servizio HTTP dell'agente. Laravel lo chiama; l'agente non accede mai direttamente al database."""

import hmac
from functools import lru_cache
from typing import Annotated, Any, Literal

import httpx
from fastapi import Depends, FastAPI, Header, HTTPException
from pydantic import BaseModel, Field

from .agent import ChatResult, ShoppingAgent
from .config import Settings, get_settings

app = FastAPI(title="Presto.it AI agent", version="0.1.0")


@lru_cache
def get_agent() -> ShoppingAgent:
    return ShoppingAgent(get_settings())


def require_token(
    settings: Annotated[Settings, Depends(get_settings)],
    authorization: Annotated[str | None, Header()] = None,
) -> None:
    """Solo Laravel può usare la chat: stesso token condiviso delle API interne."""
    if not settings.api_token:
        raise HTTPException(503, "Token non configurato (PRESTO_AI_API_TOKEN).")
    if not hmac.compare_digest(authorization or "", f"Bearer {settings.api_token}"):
        raise HTTPException(401, "Token non valido.")


class ChatRequest(BaseModel):
    message: str = Field(min_length=1, max_length=500)
    conversation_id: str | None = Field(None, max_length=64)
    lang: Literal["it", "en", "es"] = "it"


class ChatResponse(BaseModel):
    reply: str
    conversation_id: str
    articles: list[dict[str, Any]]


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


@app.post("/chat", dependencies=[Depends(require_token)])
def chat(request: ChatRequest, agent: Annotated[ShoppingAgent, Depends(get_agent)]) -> ChatResponse:
    try:
        result: ChatResult = agent.chat(request.message.strip(), request.conversation_id, request.lang)
    except (httpx.ConnectError, ConnectionError) as error:
        # Ollama spento o modello non caricabile: Laravel mostrerà "assistente non disponibile"
        raise HTTPException(503, "Il modello locale non è raggiungibile (Ollama è acceso?).") from error

    return ChatResponse(reply=result.reply, conversation_id=result.conversation_id, articles=result.articles)
