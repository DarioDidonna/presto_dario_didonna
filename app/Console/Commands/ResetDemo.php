<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

#[Signature('app:demo-reset {--force : Non chiede conferma}')]
#[Description('Riporta il sito allo stato demo: database, foto caricate e indice di ricerca da zero')]
class ResetDemo extends Command
{
    public function handle()
    {
        if ($this->laravel->environment('production')) {
            $this->error('Comando non disponibile in produzione.');

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm('Cancella TUTTO il database locale (anche il tuo account) e le foto caricate. Continuare?')) {
            $this->info('Operazione annullata.');

            return self::SUCCESS;
        }

        $this->components->task('Svuoto l\'indice di ricerca', fn () => $this->callSilently('scout:flush', ['model' => Article::class]) === 0);

        $this->components->task('Cancello foto degli annunci e foto profilo', function () {
            $disk = Storage::disk('public');

            return $disk->deleteDirectory('articles') !== false && $disk->deleteDirectory('avatars') !== false;
        });

        $this->components->task('Collegamento public/storage', fn () => $this->ensureStorageLink());

        // Ricrea le tabelle e lancia DatabaseSeeder: categorie + dati demo (che in locale indicizzano anche la ricerca)
        $this->call('migrate:fresh', ['--seed' => true, '--seeder' => 'Database\\Seeders\\DemoSeeder', '--force' => true]);

        $this->callSilently('optimize:clear');
        // Il worker della coda, se acceso, ricarica il codice e riparte pulito
        $this->callSilently('queue:restart');

        $this->newLine();
        $this->info('Demo pronta. Avvia il sito con: composer run dev');

        return self::SUCCESS;
    }

    /**
     * public/storage deve puntare a storage/app/public. Link relativo: resta valido anche spostando la cartella del progetto
     * (un link assoluto rotto, dopo uno spostamento, faceva sparire tutte le immagini).
     */
    private function ensureStorageLink(): bool
    {
        $link = public_path('storage');
        $target = storage_path('app/public');

        if (is_link($link) && realpath($link) === realpath($target)) {
            return true;
        }

        if (is_link($link)) {
            unlink($link);
        } elseif (file_exists($link)) {
            return false; // una cartella vera al posto del link: meglio non toccarla
        }

        return symlink('../storage/app/public', $link);
    }
}
