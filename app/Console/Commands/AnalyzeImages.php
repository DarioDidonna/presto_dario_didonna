<?php

namespace App\Console\Commands;

use App\Models\Image;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:analyze-images {--all : Rianalizza anche le immagini già analizzate}')]
#[Description('Rimette in coda l\'elaborazione delle immagini (oscuramento dei volti e miniature)')]
class AnalyzeImages extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $images = Image::query()
            // Volti non ancora controllati (servizio locale spento al caricamento)
            ->unless($this->option('all'), fn ($query) => $query->whereNull('faces_count'))
            ->get();

        if ($images->isEmpty()) {
            $this->info('Nessuna immagine da analizzare.');

            return self::SUCCESS;
        }

        foreach ($images as $image) {
            // Stessa catena del form: dopo l'oscuramento dei volti vanno rigenerate le miniature
            $image->process();
        }

        $this->info("Messe in coda {$images->count()} immagini: verranno elaborate dal worker (php artisan queue:work).");

        return self::SUCCESS;
    }
}
