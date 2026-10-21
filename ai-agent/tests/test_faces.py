import base64
import struct

import cv2
import numpy as np
import pytest
from fastapi.testclient import TestClient

from presto_ai_agent.app import app, get_face_detector
from presto_ai_agent.config import Settings, get_settings
from presto_ai_agent.faces import FacesRequest, expand, find_faces, read_image, yunet_detector


def encode(image: np.ndarray, ext: str = ".jpg") -> str:
    return base64.b64encode(cv2.imencode(ext, image)[1].tobytes()).decode()


def blank(width: int = 400, height: int = 300) -> np.ndarray:
    return np.full((height, width, 3), 200, np.uint8)


def finds(*boxes):
    """Rilevatore finto: restituisce sempre questi riquadri (x, y, w, h, punteggio)."""
    return lambda image, threshold: list(boxes)


def test_boxes_are_enlarged_on_every_side_and_kept_inside_the_image():
    assert expand((100, 100, 40, 40), 0.25, 400, 300) == (90, 90, 61, 61)
    # Vicino ai bordi: tagliato, mai fuori dall'immagine
    assert expand((-5, 280, 40, 40), 0.25, 400, 300) == (0, 270, 46, 30)


def test_find_faces_returns_enlarged_boxes_in_pixels_of_the_photo():
    result = find_faces(FacesRequest(image=encode(blank())), finds((100, 100, 40, 40, 0.91234)), 0.6, 0.25)

    assert (result.width, result.height) == (400, 300)
    assert [face.model_dump() for face in result.faces] == [
        {"x": 90, "y": 90, "width": 61, "height": 61, "score": 0.912}
    ]


def test_boxes_completely_outside_are_dropped():
    result = find_faces(FacesRequest(image=encode(blank())), finds((500, 500, 20, 20, 0.9)), 0.6, 0.25)

    assert result.faces == []


def test_real_model_loads_and_finds_nothing_in_an_empty_photo():
    noise = np.random.default_rng(1).integers(0, 255, (480, 640, 3), dtype=np.uint8)

    assert yunet_detector(blank(), 0.6) == []
    assert yunet_detector(noise, 0.6) == []


def test_large_photos_are_reduced_for_detection_but_boxes_use_original_pixels():
    seen = {}

    def detector(image, threshold):
        seen["shape"] = image.shape
        return [(1000, 500, 200, 200, 0.9)]

    result = find_faces(FacesRequest(image=encode(blank(3200, 2400))), detector, 0.6, 0.0)

    assert seen["shape"][:2] == (2400, 3200)  # il rilevatore riceve la foto intera: riduce lui (yunet_detector)
    assert result.faces[0].x == 1000


def with_exif_orientation(jpeg: bytes, orientation: int) -> bytes:
    """Inserisce un blocco EXIF con il solo tag Orientation subito dopo SOI."""
    tiff = b"MM\x00\x2a\x00\x00\x00\x08" + struct.pack(">H", 1) + struct.pack(">HHIHH", 0x0112, 3, 1, orientation, 0)
    tiff += b"\x00\x00\x00\x00"
    payload = b"Exif\x00\x00" + tiff
    return jpeg[:2] + b"\xff\xe1" + struct.pack(">H", len(payload) + 2) + payload + jpeg[2:]


def test_exif_rotation_is_ignored_like_gd_does_in_laravel():
    jpeg = cv2.imencode(".jpg", blank(400, 200))[1].tobytes()
    rotated = base64.b64encode(with_exif_orientation(jpeg, 6)).decode()  # "ruota di 90°"

    assert read_image(rotated).shape[:2] == (200, 400)


@pytest.fixture
def api():
    app.dependency_overrides[get_settings] = lambda: Settings(api_token="segreto")
    yield TestClient(app)
    app.dependency_overrides.clear()


def test_endpoint(api):
    app.dependency_overrides[get_face_detector] = lambda: finds((100, 100, 40, 40, 0.9))
    body = {"image": encode(blank(), ".png")}

    assert api.post("/faces", json=body).status_code == 401
    response = api.post("/faces", json=body, headers={"Authorization": "Bearer segreto"})
    assert response.json()["faces"] == [{"x": 90, "y": 90, "width": 61, "height": 61, "score": 0.9}]

    not_an_image = base64.b64encode(b"GIF89a" + b"0" * 64).decode()
    assert (
        api.post("/faces", json={"image": not_an_image}, headers={"Authorization": "Bearer segreto"}).status_code == 422
    )
