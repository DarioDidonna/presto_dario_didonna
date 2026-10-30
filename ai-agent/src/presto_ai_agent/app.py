"""Servizio HTTP dell'agente. Laravel lo chiama; l'agente non accede mai direttamente al database."""

import hmac
from functools import lru_cache
from typing import Annotated, Any, Literal

import httpx
from fastapi import Depends, FastAPI, Header, HTTPException
from langchain_ollama import ChatOllama
from pydantic import BaseModel, Field

from .agent import ChatResult, ShoppingAgent
from .config import Settings, get_settings
from .describe import Describer, DescribeRequest, DraftAd, InvalidImageError, describe_photo, ollama_describer
from .faces import Detector, FacesRequest, FacesResult, find_faces, yunet_detector
from .review import ArticleForReview, Judge, ReviewSuggestion, ollama_judge, review

# Niente /docs e /openapi.json: sono API interne, non serve descriverle a chi le raggiunge
app = FastAPI(title="Presto.it AI agent", version="0.1.0", docs_url=None, redoc_url=None, openapi_url=None)


@lru_cache
def get_agent() -> ShoppingAgent:
    return ShoppingAgent(get_settings())


@lru_cache
def get_judge() -> Judge:
    settings = get_settings()
    # Temperatura 0: per una valutazione serve la risposta più stabile possibile
    return ollama_judge(ChatOllama(model=settings.model, base_url=settings.ollama_base_url, temperature=0))


@lru_cache
def get_describer() -> Describer:
    settings = get_settings()
    return ollama_describer(ChatOllama(model=settings.vision_model, base_url=settings.ollama_base_url, temperature=0))


def get_face_detector() -> Detector:
    return yunet_detector


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
        return {
            "status": "degraded",
            "ollama": False,
            "model": settings.model,
            "model_available": False,
            "vision_model": settings.vision_model,
            "vision_model_available": False,
        }

    available = settings.model in models
    return {
        "status": "ok" if available else "degraded",
        "ollama": True,
        "model": settings.model,
        "model_available": available,
        # Facoltativo: senza, il sito funziona e manca solo "Compila con l'AI"
        "vision_model": settings.vision_model,
        "vision_model_available": settings.vision_model in models,
    }


@app.post("/chat", dependencies=[Depends(require_token)])
def chat(request: ChatRequest, agent: Annotated[ShoppingAgent, Depends(get_agent)]) -> ChatResponse:
    try:
        result: ChatResult = agent.chat(request.message.strip(), request.conversation_id, request.lang)
    except (httpx.ConnectError, ConnectionError) as error:
        # Ollama spento o modello non caricabile: Laravel mostrerà "assistente non disponibile"
        raise HTTPException(503, "Il modello locale non è raggiungibile (Ollama è acceso?).") from error

    return ChatResponse(reply=result.reply, conversation_id=result.conversation_id, articles=result.articles)


@app.post("/review", dependencies=[Depends(require_token)])
def review_article(article: ArticleForReview, judge: Annotated[Judge, Depends(get_judge)]) -> ReviewSuggestion:
    """Suggerimento per il revisore. Se il modello non risponde, si basa sulle sole regole (model_used=false)."""
    return review(article, judge)


@app.post("/describe", dependencies=[Depends(require_token)])
def describe(request: DescribeRequest, describer: Annotated[Describer, Depends(get_describer)]) -> DraftAd:
    """Bozza di annuncio dalla foto: solo fatti visibili, composti dal codice. L'utente rivede e modifica tutto."""
    try:
        return describe_photo(request, describer)
    except InvalidImageError as error:
        raise HTTPException(422, str(error)) from error
    except (httpx.ConnectError, ConnectionError) as error:
        raise HTTPException(503, "Il modello di visione non è raggiungibile (Ollama è acceso?).") from error
    except Exception as error:  # noqa: BLE001 — modello mancante o output non valido: nessuna bozza
        raise HTTPException(503, "Il modello di visione non ha prodotto una risposta valida.") from error


@app.post("/faces", dependencies=[Depends(require_token)])
def faces(
    request: FacesRequest,
    settings: Annotated[Settings, Depends(get_settings)],
    detector: Annotated[Detector, Depends(get_face_detector)],
) -> FacesResult:
    """Riquadri dei volti da oscurare (già allargati), in pixel della foto ricevuta. Non serve Ollama."""
    try:
        return find_faces(request, detector, settings.face_threshold, settings.face_margin)
    except InvalidImageError as error:
        raise HTTPException(422, str(error)) from error
