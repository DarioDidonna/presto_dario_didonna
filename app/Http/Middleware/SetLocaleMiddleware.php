<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $localeLanguage = session('locale', 'it');
        if (! in_array($localeLanguage, config('app.available_locales'), true)) {
            $localeLanguage = 'it';
        }
        App::setLocale($localeLanguage);

        return $next($request);
    }
}
