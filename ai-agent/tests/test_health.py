import httpx
import pytest
from fastapi.testclient import TestClient

from presto_ai_agent.app import app
from presto_ai_agent.config import Settings, get_settings


@pytest.fixture
def client():
    app.dependency_overrides[get_settings] = lambda: Settings(model="qwen3:4b", ollama_base_url="http://ollama.test")
    yield TestClient(app)
    app.dependency_overrides.clear()


def fake_ollama(monkeypatch, *, models=None, error=False):
    def fake_get(url, timeout):
        assert url == "http://ollama.test/api/tags"
        if error:
            raise httpx.ConnectError("Ollama spento")
        payload = {"models": [{"name": name} for name in models]}
        return httpx.Response(200, json=payload, request=httpx.Request("GET", url))

    monkeypatch.setattr(httpx, "get", fake_get)


def test_health_ok_when_model_is_available(client, monkeypatch):
    fake_ollama(monkeypatch, models=["qwen3:4b", "altro:1b"])

    assert client.get("/health").json() == {
        "status": "ok",
        "ollama": True,
        "model": "qwen3:4b",
        "model_available": True,
        "vision_model": "qwen2.5vl:3b",
        "vision_model_available": False,
    }


def test_health_reports_the_vision_model_separately(client, monkeypatch):
    # Il modello di visione è facoltativo: senza, lo stato resta "ok"
    fake_ollama(monkeypatch, models=["qwen3:4b", "qwen2.5vl:3b"])

    body = client.get("/health").json()
    assert body["status"] == "ok"
    assert body["vision_model_available"] is True


def test_health_degraded_when_model_is_missing(client, monkeypatch):
    fake_ollama(monkeypatch, models=["altro:1b"])

    body = client.get("/health").json()
    assert body["status"] == "degraded"
    assert body["model_available"] is False


def test_health_degraded_when_ollama_is_down(client, monkeypatch):
    fake_ollama(monkeypatch, error=True)

    body = client.get("/health").json()
    assert body == {
        "status": "degraded",
        "ollama": False,
        "model": "qwen3:4b",
        "model_available": False,
        "vision_model": "qwen2.5vl:3b",
        "vision_model_available": False,
    }
