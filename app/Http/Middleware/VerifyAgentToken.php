<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Le API interne sono riservate all'agente AI: richiede "Authorization: Bearer <AI_AGENT_TOKEN>".
 */
class VerifyAgentToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = (string) config('services.ai_agent.token');

        // Senza token configurato le API restano spente: mai accessibili "per sbaglio" senza protezione
        if ($token === '') {
            return response()->json(['message' => 'Agent API disabled.'], 503);
        }

        // hash_equals: confronto a tempo costante, non rivela quanti caratteri del token sono giusti
        if (! hash_equals($token, (string) $request->bearerToken())) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return $next($request);
    }
}
