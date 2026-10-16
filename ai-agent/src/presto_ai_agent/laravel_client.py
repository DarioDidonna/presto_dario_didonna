"""Client delle API interne di Laravel (/api/agent/*), in sola lettura."""

from typing import Any

import httpx

from .config import Settings


class LaravelApiError(RuntimeError):
    """Laravel non raggiungibile o risposta inattesa: l'agente lo comunica all'utente invece di inventare."""


class LaravelClient:
    def __init__(self, settings: Settings, transport: httpx.BaseTransport | None = None):
        self._http = httpx.Client(
            base_url=f"{settings.laravel_url.rstrip('/')}/api/agent",
            headers={"Authorization": f"Bearer {settings.api_token}", "Accept": "application/json"},
            timeout=10,
            transport=transport,
        )

    def _get(self, path: str, params: dict[str, Any] | None = None) -> dict[str, Any] | None:
        clean = {key: value for key, value in (params or {}).items() if value is not None}
        try:
            response = self._http.get(path, params=clean)
        except httpx.HTTPError as error:
            raise LaravelApiError(f"Laravel non raggiungibile: {error}") from error

        if response.status_code == 404:
            return None
        if response.status_code != 200:
            raise LaravelApiError(f"Risposta inattesa da Laravel ({response.status_code}) su {path}")

        return response.json()

    def categories(self, lang: str = "it") -> list[dict[str, Any]]:
        return self._get("/categories", {"lang": lang})["categories"]

    def search_articles(self, lang: str = "it", **filters: Any) -> dict[str, Any]:
        return self._get("/articles", {"lang": lang, **filters})

    def get_article(self, article_id: int, lang: str = "it") -> dict[str, Any] | None:
        body = self._get(f"/articles/{article_id}", {"lang": lang})
        return body["article"] if body else None
