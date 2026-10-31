<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Header di sicurezza su ogni risposta. La CSP non limita script e stili: Livewire e Alpine usano script inline ed
 * eval, quindi la protezione dall'XSS resta affidata all'escape di Blade. Copre il resto (iframe, form, plugin, base).
 */
class SecurityHeaders
{
    public const CSP = "base-uri 'self'; object-src 'none'; frame-ancestors 'self'; form-action 'self'";

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Content-Security-Policy', self::CSP);
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // Solo in produzione e su HTTPS: una volta inviato, il browser rifiuta l'HTTP per un anno
        if ($request->secure() && app()->isProduction()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
