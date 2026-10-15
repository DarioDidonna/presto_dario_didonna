<?php

namespace App\Providers;

use App\Models\Category;
use App\View\Composers\ErrorPageLocaleComposer;
use App\View\Composers\MarketStatsComposer;
use Illuminate\Pagination\Paginator;
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
            ['components.navbar', 'components.categories', 'livewire.article-form'],
            fn ($view) => $view->with('categories', once(
                // Conteggio dei soli annunci pubblicati, calcolato in SQL (prima: tutti gli annunci caricati per ogni categoria)
                fn () => Category::withCount(['articles as accepted_articles_count' => fn ($query) => $query->accepted()])
                    ->orderBy('name')
                    ->get()
            ))
        );

        View::composer(['components.header', 'components.live-activity'], MarketStatsComposer::class);
        View::composer('errors::*', ErrorPageLocaleComposer::class);
    }
}
