import httpx
import pytest
from fastapi.testclient import TestClient

from presto_ai_agent.agent import ChatResult
from presto_ai_agent.app import app, get_agent
from presto_ai_agent.config import Settings, get_settings

from .conftest import BIKE

AUTH = {"Authorization": "Bearer segreto"}


class StubAgent:
    def __init__(self, error: Exception | None = None):
        self.error = error
        self.calls = []

    def chat(self, message, conversation_id=None, lang="it"):
        self.calls.append((message, conversation_id, lang))
        if self.error:
            raise self.error
        return ChatResult(reply="Ecco una bici.", conversation_id=conversation_id or "conv-1", articles=[BIKE])


@pytest.fixture
def api():
    stub = StubAgent()
    app.dependency_overrides[get_settings] = lambda: Settings(api_token="segreto")
    app.dependency_overrides[get_agent] = lambda: stub
    yield TestClient(app), stub
    app.dependency_overrides.clear()


def test_chat_returns_reply_and_articles(api):
    client, stub = api

    response = client.post("/chat", json={"message": "  Cerco una bici  ", "lang": "en"}, headers=AUTH)

    assert response.status_code == 200
    assert response.json() == {"reply": "Ecco una bici.", "conversation_id": "conv-1", "articles": [BIKE]}
    assert stub.calls == [("Cerco una bici", None, "en")]


@pytest.mark.parametrize("headers", [{}, {"Authorization": "Bearer sbagliato"}, {"Authorization": "segreto"}])
def test_chat_requires_the_shared_token(api, headers):
    client, stub = api

    assert client.post("/chat", json={"message": "ciao"}, headers=headers).status_code == 401
    assert stub.calls == []


def test_chat_is_disabled_without_configured_token(api):
    client, _ = api
    app.dependency_overrides[get_settings] = lambda: Settings(api_token="")

    assert client.post("/chat", json={"message": "ciao"}, headers=AUTH).status_code == 503


@pytest.mark.parametrize(
    "payload",
    [
        {"message": ""},
        {"message": "x" * 501},
        {"message": "ciao", "lang": "de"},
        {"message": "ciao", "conversation_id": "x" * 65},
    ],
)
def test_chat_validates_input(api, payload):
    client, _ = api

    assert client.post("/chat", json=payload, headers=AUTH).status_code == 422


def test_chat_returns_503_when_ollama_is_down(api):
    client, _ = api
    app.dependency_overrides[get_agent] = lambda: StubAgent(error=httpx.ConnectError("Ollama spento"))

    response = client.post("/chat", json={"message": "ciao"}, headers=AUTH)

    assert response.status_code == 503
    assert "Ollama" in response.json()["detail"]
