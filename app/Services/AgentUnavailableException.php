<?php

namespace App\Services;

use RuntimeException;

/**
 * L'agente AI non può rispondere (servizio o Ollama spenti, risposta non valida): la chat lo dice all'utente.
 */
class AgentUnavailableException extends RuntimeException {}
