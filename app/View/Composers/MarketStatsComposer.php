<?php

namespace App\View\Composers;

use App\Models\Article;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Statistiche reali mostrate in homepage (header "Tech Tracker" e sezione "Live").
 */
class MarketStatsComposer
{
    public function compose(View $view): void
    {
        $view->with('stats', self::stats());
    }

    // Metodo statico: once() memorizza per istanza e Laravel crea un composer per ogni vista,
    // quindi header e sezione "Live" ricalcolavano tutto due volte
    private static function stats(): array
    {
        return once(function () {
            $counts = Article::accepted()
                ->selectRaw('count(*) as total')
                ->selectRaw('sum(case when created_at >= ? then 1 else 0 end) as last_week', [now()->subWeek()])
                ->toBase()
                ->first();

            return [
                'published_articles' => (int) $counts->total,
                'online_users' => self::onlineUsers(),
                'articles_last_week' => (int) $counts->last_week,
                'pending_articles' => Article::toBeRevisedCount(),
                'latest_articles' => Article::accepted()->with('category')->latest()->take(4)->get(),
            ];
        });
    }

    /**
     * Sessioni attive negli ultimi 5 minuti (utenti e ospiti). Disponibile solo con SESSION_DRIVER=database;
     * negli altri casi si ripiega sul numero di utenti registrati.
     */
    private static function onlineUsers(): int
    {
        if (config('session.driver') !== 'database') {
            return User::count();
        }

        return DB::table(config('session.table', 'sessions'))
            ->where('last_activity', '>=', now()->subMinutes(5)->getTimestamp())
            ->count();
    }
}
