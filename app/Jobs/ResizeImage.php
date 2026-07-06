<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Spatie\Image\Enums\CropPosition;
use Spatie\Image\Enums\ImageDriver;
use Spatie\Image\Enums\Unit;
use Spatie\Image\Image;

class ResizeImage implements ShouldQueue
{
    use Queueable;

    private $w, $h, $fileName, $path;

    /**
     * Create a new job instance.
     */
    public function __construct($filePath, $w, $h)
    {
        $this->path = dirname($filePath);
        $this->fileName = basename($filePath);
        $this->w = $w;
        $this->h = $h;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $w = $this->w;
        $h = $this->h;

        // 1. Costruiamo i percorsi assoluti puliti
        $srcPath = storage_path('app/public/' . $this->path . '/' . $this->fileName);
        $destPath = storage_path('app/public/' . $this->path . "/crop_{$w}x{$h}_" . $this->fileName);

        // 2. DEBUG CRUCIALE: Scriviamo nei log dove stiamo cercando il file prima del crash
        logger()->info("ResizeImage: Tento di caricare il file da: " . $srcPath);

        // 3. Controllo preventivo dell'esistenza del file originale
        if (!file_exists($srcPath)) {
            logger()->error("ResizeImage FALLITO: Il file fisico NON ESISTE al percorso indicato sopra.");
            return; // Evita il FAIL bloccando l'esecuzione in modo pulito
        }

        // 4. Controllo del Watermark
        $watermarkPath = base_path('resources/img/watermark.png');
        if (!file_exists($watermarkPath)) {
            logger()->error("ResizeImage FALLITO: Manca il file del watermark in: " . $watermarkPath);
            return;
        }

        try {
    $image = Image::useImageDriver(ImageDriver::Gd)->load($srcPath);

    $image->resize(
        width: $w,
        height: $h,
        constraints: [
            \Spatie\Image\Enums\Constraint::PreserveAspectRatio,
            \Spatie\Image\Enums\Constraint::DoNotUpsize // Evita di sgranare se l'immagine è più piccola di $w e $h
        ]
    );

    $image->save($destPath);
        
    logger()->info("ResizeImage COMPLETATO con successo (Proporzionale) per: " . $this->fileName);
    
} catch (\Exception $e) {
    logger()->error("ResizeImage ERRORE LIBRERIA: " . $e->getMessage());
}
    }
}
