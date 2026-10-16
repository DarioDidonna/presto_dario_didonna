"""Controlli deterministici attorno al modello: un modello piccolo non è affidabile su queste regole da solo.

- argomenti dei tool ancorati al messaggio dell'utente (niente prezzi inventati)
- testi degli annunci (scritti dagli utenti) ripuliti dai tentativi di prompt injection
- risposta finale senza link inventati e senza prezzi che non corrispondono agli annunci trovati
"""

import re
from contextvars import ContextVar
from typing import Any

# Messaggio dell'utente del turno corrente (ContextVar: sicuro anche con richieste concorrenti in thread diversi)
current_user_message: ContextVar[str] = ContextVar("current_user_message", default="")

INJECTION_PATTERNS = re.compile(
    r"ignor\w*\s+(tutte\s+)?(le\s+)?(istruzion|regol|indicazion)|ignore\s+(all\s+)?(previous|prior|the)\s+instruction"
    r"|system\s*prompt|prompt\s+di\s+sistema|sei\s+ora\s|you\s+are\s+now|rispondi\s+(sempre\s+)?che|answer\s+that",
    re.IGNORECASE,
)

PRICE_IN_TEXT = re.compile(r"(\d+(?:[.,]\d+)*)\s*(?:€|euro\b|eur\b)|€\s*(\d+(?:[.,]\d+)*)", re.IGNORECASE)
MARKDOWN_LINK = re.compile(r"\[([^\]]*)\]\([^)]*\)")
BARE_URL = re.compile(r"https?://\S+")


def parse_number(raw: str) -> float:
    """ "1.200" e "1.200,50" all'italiana, "480.00" e "12,5" come decimali."""
    if "," in raw:
        return float(raw.replace(".", "").replace(",", "."))
    if re.fullmatch(r"\d{1,3}(\.\d{3})+", raw):
        return float(raw.replace(".", ""))
    return float(raw)


def numbers_in(text: str) -> set[float]:
    """Numeri presenti in un testo ("500 euro", "50€", "1.200") come float."""
    return {parse_number(raw) for raw in re.findall(r"\d+(?:[.,]\d+)*", text)}


def price_requested(value: float | None) -> bool:
    """Un filtro di prezzo vale solo se l'utente ha scritto quel numero nel messaggio corrente."""
    return value is not None and value in numbers_in(current_user_message.get())


REFUSALS = {
    "it": "Posso aiutarti solo a cercare annunci su Presto.it. Dimmi cosa stai cercando!",
    "en": "I can only help you find ads on Presto.it. Tell me what you are looking for!",
    "es": "Solo puedo ayudarte a buscar anuncios en Presto.it. ¡Dime qué estás buscando!",
}

# Promemoria aggiunto al messaggio dell'utente (non al prompt di sistema): i modelli piccoli lo seguono molto di più
SEARCH_IN_ITALIAN_HINT = "\n\n(Nota per l'assistente: il catalogo è in italiano, cerca con parole chiave in italiano.)"


def is_injection_attempt(message: str) -> bool:
    return bool(INJECTION_PATTERNS.search(message))


def sanitize_user_content(text: str | None) -> str | None:
    if text and INJECTION_PATTERNS.search(text):
        return "[testo nascosto: conteneva istruzioni rivolte all'assistente]"
    return text


def strip_links(reply: str) -> str:
    """Le schede degli annunci forniscono già i link veri: dalla risposta si tolgono quelli (spesso inventati)."""
    reply = MARKDOWN_LINK.sub(r"\1", reply)
    return BARE_URL.sub("", reply).strip()


def prices_are_grounded(reply: str, articles: list[dict[str, Any]]) -> bool:
    """Ogni prezzo citato nella risposta deve corrispondere a un annuncio trovato o a un numero detto dall'utente."""
    allowed = {round(float(a["price"]), 2) for a in articles} | numbers_in(current_user_message.get())
    for before, after in PRICE_IN_TEXT.findall(reply):
        if round(parse_number(before or after), 2) not in allowed:
            return False
    return True


def summary_from_articles(articles: list[dict[str, Any]], lang: str) -> str:
    """Risposta di riserva costruita solo dai dati veri."""
    intro = {"it": "Ecco cosa ho trovato:", "en": "Here is what I found:", "es": "Esto es lo que he encontrado:"}
    lines = [intro.get(lang, intro["it"])]
    lines += [f"- {a['title']}: {a['price']:.2f} €" for a in articles]
    return "\n".join(lines)
