"""Valutazione della bozza di annuncio da foto con il modello di visione VERO (Ollama).

    uv run python evals/describe.py

Usa foto libere di Wikimedia Commons, scaricate al primo avvio in evals/.cache/ (non nel repository).
Per ogni foto controlla: oggetto riconosciuto, categoria accettabile, testo senza dati inventati, italiano.
"""

import base64
import pathlib
import re
import statistics
import time
import urllib.parse
import urllib.request

from langchain_ollama import ChatOllama

from presto_ai_agent.config import get_settings
from presto_ai_agent.describe import FORBIDDEN, INVENTED, DescribeRequest, describe_photo, ollama_describer

CATEGORIES = [
    "Abbigliamento", "Accessori", "Animali domestici", "Casa e Giardinaggio", "Elettronica",
    "Giocattoli", "Libri e Riviste", "Motori", "Salute e Bellezza", "Sport",
]  # fmt: skip

# (file su Wikimedia Commons, parole accettate per l'oggetto, categorie accettabili o None se nessuna è adatta)
CASES = [
    ("Guitar_May_2009-1.jpg", {"chitarra"}, None),  # il sito non ha una categoria per gli strumenti musicali
    ("2023_Pluszowy_miś.jpg", {"orso", "orsetto", "orsacchiotto", "peluche"}, {"Giocattoli"}),
    ("Couch-furniture-living-room-sofa_(24300293356).jpg", {"divano", "sofà"}, {"Casa e Giardinaggio"}),
    ("Vans_sneakers_and_socks.jpg", {"scarpe", "sneakers", "calzini", "calze"}, {"Abbigliamento", "Accessori"}),
    ("Full-Face_Helmet_9274.jpg", {"casco"}, {"Motori", "Sport", "Accessori"}),
    ("A_One_Laptop_per_Child_computer_(1273754008).jpg", {"computer", "portatile", "laptop"}, {"Elettronica"}),
    ("Self-winding_wristwatch_(transparent_backside).jpg", {"orologio"}, {"Accessori"}),
    ("2023_Czajnik_elektryczny_N'OVEEN.jpg", {"bollitore"}, {"Casa e Giardinaggio", "Elettronica"}),
]  # fmt: skip

# Parole inglesi frequenti nelle risposte sbagliate di lingua ("Couch", "black", "leather")
ENGLISH = re.compile(r"\b(couch|black|white|leather|wood|wooden|with|and|the)\b", re.IGNORECASE)
CACHE = pathlib.Path(__file__).parent / ".cache" / "describe"


def photo(name: str) -> bytes:
    path = CACHE / name
    if not path.exists():
        CACHE.mkdir(parents=True, exist_ok=True)
        url = "https://commons.wikimedia.org/wiki/Special:FilePath/" + urllib.parse.quote(name) + "?width=768"
        request = urllib.request.Request(url, headers={"User-Agent": "PrestoEval/1.0 (valutazione didattica)"})
        with urllib.request.urlopen(request, timeout=30) as response:
            path.write_bytes(response.read())
    return path.read_bytes()


def main() -> None:
    settings = get_settings()
    describer = ollama_describer(
        ChatOllama(model=settings.vision_model, base_url=settings.ollama_base_url, temperature=0)
    )
    scores = {"oggetto": 0, "categoria": 0, "pulito": 0, "italiano": 0}
    scored_categories = 0
    times = []

    for name, words, categories in CASES:
        image = base64.b64encode(photo(name)).decode()
        start = time.time()
        draft = describe_photo(DescribeRequest(image=image, categories=CATEGORIES), describer)
        times.append(time.time() - start)

        text = f"{draft.title} {draft.description}"
        checks = {
            "oggetto": draft.found and any(word in draft.title.lower() for word in words),
            "categoria": categories is not None and draft.category in categories,
            "pulito": not (FORBIDDEN.search(text) or INVENTED.search(draft.description)),
            "italiano": not ENGLISH.search(text),
        }
        scored_categories += categories is not None
        for key, ok in checks.items():
            scores[key] += ok
        marks = " ".join(f"{key}{'✓' if ok else '✗'}" for key, ok in checks.items() if key != "categoria" or categories)
        print(f"{times[-1]:5.1f}s {marks}\n       {draft.title} | {draft.category} | {draft.description}")

    total = len(CASES)
    print(
        f"\n{settings.vision_model}: oggetto {scores['oggetto']}/{total}, "
        f"categoria {scores['categoria']}/{scored_categories}, "
        f"testo pulito {scores['pulito']}/{total}, italiano {scores['italiano']}/{total}; "
        f"prima foto {times[0]:.1f}s (caricamento), poi mediana {statistics.median(times[1:]):.1f}s"
    )


if __name__ == "__main__":
    main()
