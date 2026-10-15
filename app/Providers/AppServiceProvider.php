<?php

namespace App\Providers;

use App\Models\Category;
use App\View\Composers\ErrorPageLocaleComposer;
use App\View\Composers\MarketStatsComposer;
use Illuminate\Auth\Events\Login;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        View::composer('errors::*', ErrorPageLocaleComposer::class);

        // Al login si ripristina la lingua scelta l'ultima volta dall'utente
        Event::listen(Login::class, function (Login $event) {
            if ($event->user->locale && request()->hasSession()) {
                request()->session()->put('locale', $event->user->locale);
            }
        });
    }
}
