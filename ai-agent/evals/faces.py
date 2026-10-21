"""Valutazione del rilevatore di volti locale (YuNet) sulle foto vere.

    uv run python evals/faces.py

- Persone: foto libere di Wikimedia Commons (scaricate al primo avvio in evals/.cache/), volti contati a mano.
- Oggetti: le 24 foto demo del sito (database/seeders/demo-images), dove non deve trovare nessun volto.
Usa la stessa soglia del servizio (PRESTO_AI_FACE_THRESHOLD).
"""

import pathlib
import statistics
import time
import urllib.parse
import urllib.request

import cv2

from presto_ai_agent.config import get_settings
from presto_ai_agent.faces import yunet_detector

# (file su Wikimedia Commons, volti visibili contati a mano)
PEOPLE = [
    ("Secretary Blinken Poses for a Photo With the Group of Friends of Georgia (51718610267).jpg", 19),
    ("Dagestani man and woman.jpg", 2),
    (
        "DSCF0914 A smiling vendor flashes a peace sign behind a colorful display of bottled drinks at a busy "
        "night market stall.jpg",
        1,
    ),
    ("Water reflection of a smiling woman planting rice.jpg", 1),
    ("British 19th Century, Portrait of a Child, c. 1850, NGA 102580.jpg", 1),
    # Caso difficile tenuto apposta: profilo, occhiali da sole, testa all'insù
    ("Profile portrait of a man wearing sunglasses and hat (AM 81807-1).jpg", 1),
]
OBJECTS = pathlib.Path(__file__).resolve().parents[2] / "database" / "seeders" / "demo-images"
CACHE = pathlib.Path(__file__).parent / ".cache" / "faces"


def photo(name: str) -> pathlib.Path:
    path = CACHE / name
    if not path.exists():
        CACHE.mkdir(parents=True, exist_ok=True)
        url = "https://commons.wikimedia.org/wiki/Special:FilePath/" + urllib.parse.quote(name) + "?width=1200"
        request = urllib.request.Request(url, headers={"User-Agent": "PrestoEval/1.0 (valutazione didattica)"})
        with urllib.request.urlopen(request, timeout=30) as response:
            path.write_bytes(response.read())
    return path


def main() -> None:
    threshold = get_settings().face_threshold
    times = []

    found = expected = 0
    print("Persone")
    for name, faces in PEOPLE:
        image = cv2.imread(str(photo(name)))
        start = time.time()
        boxes = yunet_detector(image, threshold)
        times.append((time.time() - start) * 1000)
        # Un riquadro in più su una foto di persone non vale come volto trovato
        found += min(len(boxes), faces)
        expected += faces
        print(f"  {len(boxes):2}/{faces:2}  {name[:70]}")

    false_alarms = []
    for path in sorted(OBJECTS.glob("*.jpg")):
        start = time.time()
        boxes = yunet_detector(cv2.imread(str(path)), threshold)
        times.append((time.time() - start) * 1000)
        if boxes:
            false_alarms.append(f"{path.stem} ({len(boxes)})")
    total_objects = len(list(OBJECTS.glob("*.jpg")))
    print(f"Oggetti con falsi volti: {', '.join(false_alarms) or 'nessuno'}")

    print(
        f"\nSoglia {threshold}: volti trovati {found}/{expected}, foto di oggetti con falsi volti "
        f"{len(false_alarms)}/{total_objects}, mediana {statistics.median(times):.0f} ms a foto"
    )


if __name__ == "__main__":
    main()
