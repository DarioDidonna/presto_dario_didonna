<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\User;
use App\View\Composers\ErrorPageLocaleComposer;
use App\View\Composers\FooterStatusComposer;
use App\View\Composers\MarketStatsComposer;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Paginator::useBootstrapFive();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Un limite con nome per ogni azione: i "throttle:N,M" senza nome condividono un solo contatore per utente,
        // e bastavano pochi caricamenti o verifiche per bloccare la candidatura o la cancellazione dell'account
        foreach ([
            'revisor-request' => Limit::perHour(3),
            'revisor-confirm' => Limit::perMinute(10),
            'avatar' => Limit::perMinute(10),
            'profile-export' => Limit::perMinute(5),
            'profile-destroy' => Limit::perMinute(5),
            'checkout' => Limit::perMinute(5),
            'review' => Limit::perMinute(10),
            'verification' => Limit::perMinute(6),
            'agent-api' => Limit::perMinute(120),
        ] as $name => $limit) {
            RateLimiter::for($name, fn (Request $request) => (clone $limit)->by($request->user()?->id ?: $request->ip()));
        }

        // In produzione anche il controllo sulle password già finite in un furto di dati (Have I Been Pwned riceve
        // solo i primi 5 caratteri dell'hash, mai la password)
        Password::defaults(fn () => $this->app->isProduction() ? Password::min(12)->uncompromised() : Password::min(8));

        // In produzione tutti i link generati sono HTTPS
        URL::forceHttps($this->app->isProduction());

        View::composer(
            ['components.navbar', 'components.categories', 'livewire.article-form', 'article.index'],
            fn ($view) => $view->with('categories', once(
                // Conteggio dei soli annunci pubblicati, calcolato in SQL (prima: tutti gli annunci caricati per ogni categoria)
                fn () => Category::withCount(['articles as accepted_articles_count' => fn ($query) => $query->accepted()])
                    ->orderBy('name')
                    ->get()
            ))
        );

        View::composer(['components.header', 'components.live-activity'], MarketStatsComposer::class);
        View::composer('components.footer', FooterStatusComposer::class);
        View::composer('errors::*', ErrorPageLocaleComposer::class);

        // Link di reset sempre su APP_URL, mai sull'host della richiesta: un Host falso porterebbe il token a un altro sito
        ResetPassword::createUrlUsing(fn (User $user, string $token) => rtrim((string) config('app.url'), '/').route('password.reset', [
            'token' => $token,
            'email' => $user->getEmailForPasswordReset(),
        ], false));

        // Al login si ripristina la lingua scelta l'ultima volta dall'utente
        Event::listen(Login::class, function (Login $event) {
            if ($event->user instanceof User && $event->user->locale && request()->hasSession()) {
                request()->session()->put('locale', $event->user->locale);
            }
        });
    }
}
