"""Configurazione del servizio, letta da variabili d'ambiente (prefisso PRESTO_AI_) o dal file .env."""

from functools import lru_cache

from pydantic_settings import BaseSettings, SettingsConfigDict


class Settings(BaseSettings):
    model_config = SettingsConfigDict(env_prefix="PRESTO_AI_", env_file=".env", extra="ignore")

    # Modello locale servito da Ollama: piccolo per stare negli 8 GB di RAM insieme al resto della demo
    ollama_base_url: str = "http://localhost:11434"
    # qwen2.5:3b: 1-2 s per risposta e ~2,2 GB su un M3 con 8 GB (qwen3:4b: 17-73 s, va in swap)
    model: str = "qwen2.5:3b"
    temperature: float = 0.2
    # Modello di visione per la bozza di annuncio dalla foto: ~3,3 GB, 3 s a foto se già caricato (12-16 s se
    # Ollama deve alternarlo con il modello della chat: in 8 GB non stanno insieme)
    vision_model: str = "qwen2.5vl:3b"

    # API interne di Laravel (sola lettura) usate dai tool dell'agente
    laravel_url: str = "http://localhost:8000"
    api_token: str = ""

    host: str = "127.0.0.1"
    port: int = 8001


@lru_cache
def get_settings() -> Settings:
    return Settings()
