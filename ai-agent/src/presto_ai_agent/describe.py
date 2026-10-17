"""Bozza di annuncio a partire da una foto, con un modello di visione locale.

Misurato con qwen2.5vl:3b (8 foto): lasciato libero di scrivere l'annuncio inventava misure, taglie, condizioni,
un prezzo e perfino un numero di telefono. Qui il modello fa solo ciò che sa fare: dire cosa vede (oggetto, marca
leggibile, colore, dettagli) e proporre una categoria. Il codice ripulisce quei fatti e compone titolo e descrizione,
quindi nel testo non può comparire nulla che il modello non abbia "visto". La categoria resta una proposta da
controllare (giusta circa 5 volte su 8): decide sempre l'utente, che può modificare tutto.
"""

import base64
import binascii
import re
from collections.abc import Callable
from typing import Literal

from langchain_core.language_models import BaseChatModel
from langchain_core.messages import HumanMessage
from pydantic import BaseModel, Field

from .guardrails import INJECTION_PATTERNS

Lang = Literal["it", "en", "es"]

MAX_IMAGE_BYTES = 6 * 1024 * 1024
IMAGE_SIGNATURES = {b"\xff\xd8\xff": "jpeg", b"\x89PNG\r\n\x1a\n": "png", b"RIFF": "webp"}


class PhotoFacts(BaseModel):
    """Fatti visti dal modello, dopo la validazione."""

    object: str
    brand: str = ""
    color: str = ""
    details: list[str] = Field(default_factory=list)
    category: str = ""


def facts_schema(categories: list[str]) -> dict:
    """Schema JSON per l'output strutturato di Ollama. Tutti i campi obbligatori e la categoria come lista chiusa:
    con campi facoltativi il modello piccolo lasciava vuoti colore e categoria."""
    text = {"type": "string"}
    return {
        "title": "PhotoFacts",
        "description": "Ciò che si vede nella foto",
        "type": "object",
        "properties": {
            "object": text,
            "brand": text,
            "color": text,
            "details": {"type": "array", "items": text, "maxItems": 3},
            "category": {"type": "string", "enum": categories},
        },
        "required": ["object", "brand", "color", "details", "category"],
    }


class DescribeRequest(BaseModel):
    image: str = Field(min_length=16, description="Foto in base64 (JPEG, PNG o WebP)")
    categories: list[str] = Field(min_length=1, max_length=50)
    lang: Lang = "it"


class DraftAd(BaseModel):
    found: bool
    title: str = ""
    description: str = ""
    category: str | None = None
    facts: PhotoFacts | None = None


Describer = Callable[[str, list[str], Lang], PhotoFacts]


class InvalidImageError(ValueError):
    pass


def decode_image(data: str) -> str:
    """Controlla che il base64 sia una foto vera e non troppo grande; restituisce il base64 ripulito."""
    data = data.split(",", 1)[1] if data.startswith("data:") else data
    try:
        raw = base64.b64decode(data, validate=True)
    except (binascii.Error, ValueError) as error:
        raise InvalidImageError("Immagine non valida.") from error
    if len(raw) > MAX_IMAGE_BYTES:
        raise InvalidImageError("Immagine troppo grande.")
    if not any(raw.startswith(signature) for signature in IMAGE_SIGNATURES):
        raise InvalidImageError("Formato non supportato: servono JPEG, PNG o WebP.")
    return data


PROMPTS = {
    "it": (
        "Guarda la foto di un oggetto messo in vendita e rispondi in italiano, solo con ciò che si vede davvero. "
        "object: il nome comune dell'oggetto principale in italiano (non le persone). brand: la marca solo se è "
        "scritta nella foto, altrimenti stringa vuota. color: il colore principale. details: al massimo 3 "
        "caratteristiche visibili (materiale, forma, parti). Niente misure, prezzi, taglie, condizioni o contatti. "
        "category: la categoria più adatta per un sito di annunci."
    ),
    "en": (
        "Look at the photo of an item for sale and answer in English, only with what is actually visible. "
        "object: the common name of the main item (not people). brand: only if it is written in the photo, "
        "otherwise an empty string. color: the main colour. details: at most 3 visible features (material, shape, "
        "parts). No measurements, prices, sizes, condition or contacts. category: the most suitable one."
    ),
    "es": (
        "Mira la foto de un objeto en venta y responde en español, solo con lo que se ve de verdad. "
        "object: el nombre común del objeto principal (no las personas). brand: solo si está escrita en la foto, "
        "si no una cadena vacía. color: el color principal. details: como máximo 3 características visibles "
        "(material, forma, partes). Nada de medidas, precios, tallas, estado o contactos. category: la más adecuada."
    ),
}


def ollama_describer(model: BaseChatModel) -> Describer:
    def describe(image: str, categories: list[str], lang: Lang) -> PhotoFacts:
        # Schema costruito a ogni richiesta: la lista chiusa delle categorie arriva da Laravel
        structured = model.with_structured_output(facts_schema(categories), method="json_schema")
        message = HumanMessage(
            content=[
                {"type": "text", "text": PROMPTS[lang]},
                {"type": "image_url", "image_url": f"data:image/jpeg;base64,{image}"},
            ]
        )
        return PhotoFacts.model_validate(structured.invoke([message]))

    return describe


# Mai in un annuncio generato: prezzi, contatti, link (vale per tutti i campi)
FORBIDDEN = re.compile(r"\d+\s*(?:€|euro\b|eur\b)|€|prezzo|price|precio|whatsapp|@|https?://|www\.", re.IGNORECASE)
# Ciò che non si può sapere da una foto (solo nei dettagli: "Bosch WGB24400IT" nella marca va bene)
INVENTED = re.compile(
    r"\d+\s*(?:cm|mm|kg|g\b|m\b|l\b|litri|gb|tb)"
    r"|taglia|\bsize\b|talla|condizion|condition|estado|nuov[oa]|\bnew\b|nuev[oa]|usat[oa]|\bused\b|usad[oa]"
    r"|ottimo stato|contatt|contact",
    re.IGNORECASE,
)
# "forma: cilindrica" → "cilindrica": il modello a volte ripete il nome della caratteristica chiesta
LABEL_PREFIX = re.compile(
    r"^(?:forma|parti?|materiale|colore|shape|parts?|material|colou?r|partes?)\s*:\s*", re.IGNORECASE
)
PHONE = re.compile(r"\+?\d[\d\s.-]{7,}\d")
# Risposte che significano "nessuna marca": il modello le scrive invece di lasciare il campo vuoto
NO_BRAND = re.compile(r"^(non\b|nessun|n/?a$|none|no\b|unknown|sconosciut|desconocid|ninguna|-+$)", re.IGNORECASE)


def clean(text: str, max_length: int, strict: bool = False) -> str:
    text = re.sub(r"\s+", " ", text or "").strip(" .,;:-")
    if not text or INJECTION_PATTERNS.search(text) or FORBIDDEN.search(text) or PHONE.search(text):
        return ""
    if strict and INVENTED.search(text):
        return ""
    return text[:max_length].strip()


def clean_facts(facts: PhotoFacts) -> PhotoFacts:
    brand = clean(facts.brand, 40)
    details: list[str] = []
    for detail in facts.details:
        detail = clean(LABEL_PREFIX.sub("", detail.strip()), 80, strict=True)
        if detail and detail.lower() not in {d.lower() for d in details} and detail.lower() != facts.object.lower():
            details.append(detail)
    return PhotoFacts(
        object=clean(facts.object, 40),
        brand="" if NO_BRAND.search(brand) else brand,
        color=clean(facts.color, 30, strict=True),
        details=details[:3],
        category=facts.category.strip(),
    )


def match_category(proposed: str, categories: list[str]) -> str | None:
    """Solo una delle categorie del sito; altrimenti nessuna proposta (la sceglie l'utente)."""
    wanted = proposed.strip().lower()
    return next((category for category in categories if category.lower() == wanted), None)


TEMPLATES = {
    "it": {"color": "Colore: {color}.", "details": "Caratteristiche visibili: {details}."},
    "en": {"color": "Colour: {color}.", "details": "Visible features: {details}."},
    "es": {"color": "Color: {color}.", "details": "Características visibles: {details}."},
}


def compose(facts: PhotoFacts, lang: Lang) -> tuple[str, str]:
    name = facts.object[:1].upper() + facts.object[1:]
    title = f"{name} {facts.brand}".strip()

    sentences = [f"{title}."]
    if facts.color:
        sentences.append(TEMPLATES[lang]["color"].format(color=facts.color.lower()))
    if facts.details:
        sentences.append(TEMPLATES[lang]["details"].format(details=", ".join(d.lower() for d in facts.details)))
    return title, " ".join(sentences)


def describe_photo(request: DescribeRequest, describer: Describer) -> DraftAd:
    """Errori del modello (spento, output non valido) arrivano al chiamante: senza modello non c'è bozza."""
    image = decode_image(request.image)
    facts = clean_facts(describer(image, request.categories, request.lang))
    if not facts.object:
        return DraftAd(found=False)

    title, description = compose(facts, request.lang)
    return DraftAd(
        found=True,
        title=title,
        description=description,
        category=match_category(facts.category, request.categories),
        facts=facts,
    )
