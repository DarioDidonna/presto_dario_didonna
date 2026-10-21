"""Rilevamento dei volti in locale con YuNet (OpenCV), per oscurarli nelle foto degli annunci.

Modello da 230 KB incluso nel pacchetto (licenza MIT, models/LICENSE-yunet): pochi millisecondi a foto, senza GPU,
senza rete. Misurato prima di scriverlo: trova i volti rivolti verso l'obiettivo anche piccoli o in penombra, ma
non quelli di spalle o di profilo stretto, e a volte scambia per un volto un oggetto (i bottoncini di un mattoncino).
Nessuna soglia separa i due casi: si privilegia la privacy (soglia bassa, riquadri allargati) e il numero di volti
oscurati si mostra al revisore, che controlla ogni foto prima della pubblicazione.
"""

import base64
from collections.abc import Callable
from pathlib import Path

import cv2
import numpy as np
from pydantic import BaseModel, Field

from .describe import InvalidImageError, decode_image

MODEL = Path(__file__).parent / "models" / "face_detection_yunet_2023mar.onnx"
# Le foto più grandi si riducono per il rilevamento: i riquadri tornano poi alle coordinate originali
MAX_SIDE = 1600


class FacesRequest(BaseModel):
    image: str = Field(min_length=16, description="Foto in base64 (JPEG, PNG o WebP)")


class FaceBox(BaseModel):
    x: int
    y: int
    width: int
    height: int
    score: float


class FacesResult(BaseModel):
    width: int
    height: int
    faces: list[FaceBox]


# (immagine BGR, soglia) -> [(x, y, larghezza, altezza, punteggio)] in pixel dell'immagine ricevuta
Detector = Callable[[np.ndarray, float], list[tuple[float, float, float, float, float]]]


def yunet_detector(image: np.ndarray, threshold: float) -> list[tuple[float, float, float, float, float]]:
    height, width = image.shape[:2]
    scale = min(1.0, MAX_SIDE / max(height, width))
    small = cv2.resize(image, (round(width * scale), round(height * scale))) if scale < 1 else image

    # Un rilevatore per richiesta: costa pochi millisecondi ed evita di condividerlo tra thread
    detector = cv2.FaceDetectorYN.create(str(MODEL), "", (small.shape[1], small.shape[0]), threshold, 0.3, 5000)
    _, faces = detector.detect(small)
    if faces is None:
        return []
    return [(f[0] / scale, f[1] / scale, f[2] / scale, f[3] / scale, float(f[14])) for f in faces]


def read_image(data: str) -> np.ndarray:
    """Pixel così come sono nel file, senza applicare la rotazione EXIF: Laravel disegna i riquadri con GD, che
    l'ignora, quindi le coordinate devono riferirsi alla stessa griglia (Laravel raddrizza la foto prima di inviarla).
    """
    flags = cv2.IMREAD_COLOR | cv2.IMREAD_IGNORE_ORIENTATION
    image = cv2.imdecode(np.frombuffer(base64.b64decode(decode_image(data)), np.uint8), flags)
    if image is None:
        raise InvalidImageError("Immagine non leggibile.")
    return image


def expand(box: tuple[float, float, float, float], margin: float, width: int, height: int) -> tuple[int, ...]:
    """Riquadro allargato di `margin` per lato (capelli, orecchie, mento) e tenuto dentro l'immagine."""
    x, y, w, h = box
    left, top = max(0, int(x - w * margin)), max(0, int(y - h * margin))
    right, bottom = min(width, int(x + w * (1 + margin)) + 1), min(height, int(y + h * (1 + margin)) + 1)
    return left, top, right - left, bottom - top


def find_faces(request: FacesRequest, detector: Detector, threshold: float, margin: float) -> FacesResult:
    image = read_image(request.image)
    height, width = image.shape[:2]
    faces = []
    for x, y, w, h, score in detector(image, threshold):
        left, top, box_width, box_height = expand((x, y, w, h), margin, width, height)
        if box_width > 0 and box_height > 0:
            faces.append(FaceBox(x=left, y=top, width=box_width, height=box_height, score=round(score, 3)))
    return FacesResult(width=width, height=height, faces=faces)
