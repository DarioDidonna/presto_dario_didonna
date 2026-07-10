<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Spatie\Image\Enums\CropPosition;
use Spatie\Image\Enums\Unit;
use Spatie\Image\Enums\ImageDriver;

class ResizeImage implements ShouldQueue
{
    use Queueable;

    private $w, $h, $fileName, $path, $fullPathForDb;

    /**
     * Create a new job instance.
     */
    public function __construct($filePath, $w, $h)
    {
        // Ci salviamo il percorso intero originale per fare la query sul database dopo
        $this->fullPathForDb = $filePath; 
        
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

        $srcPath = storage_path('app/public/' . $this->path . '/' . $this->fileName);
        $destPath = storage_path('app/public/' . $this->path . "/crop_{$w}x{$h}_" . $this->fileName);

        if (!file_exists($srcPath)) {
            logger()->error("ResizeImage: File non trovato in " . $srcPath);
            return;
        }

        try {
            // Usiamo il metodo fit specificando i parametri con i nomi corretti per la V3
            $image = \Spatie\Image\Image::useImageDriver(\Spatie\Image\Enums\ImageDriver::Gd)
                ->load($srcPath)
                ->fit(
                    \Spatie\Image\Enums\Fit::Crop, 
                    $w, 
                    $h
                );

            // 2. Definiamo il percorso del watermark
            $watermarkPath = base_path('resources/img/watermark.png');
            
            if (file_exists($watermarkPath)) {
                // Posizioniamo il watermark in alto a destra (TopRight)
                $image->watermark(
                    $watermarkPath,
                    position: \Spatie\Image\Enums\AlignPosition::TopRight,
                    width: 50,
                    height: 50,
                    paddingX: 10, // 10 pixel di distanza dal bordo destro
                    paddingY: 10, // 10 pixel di distanza dal bordo superiore
                    paddingUnit: \Spatie\Image\Enums\Unit::Pixel
                );
                logger()->info("ResizeImage: Watermark TopRight applicato in memoria.");
            } else {
                logger()->warning("ResizeImage: FILE WATERMARK MANCANTE in " . $watermarkPath);
            }

            // 3. Rimuoviamo la vecchia miniatura se esisteva già, per evitare blocchi del Mac
            if (file_exists($destPath)) {
                @unlink($destPath);
            }

            // 4. Salviamo la miniatura finale ritagliata e con watermark
            $image->save($destPath);
            logger()->info("ResizeImage: Miniatura salvata fisicamente in: " . $destPath);

            // 5. Aggiorniamo il database per renderla visibile nel Blade condizionale
            $pureFileName = $this->fileName;
            $imageModel = \App\Models\Image::where('path', 'LIKE', '%' . $pureFileName)->first();

            if ($imageModel) {
                $imageModel->update(['is_processed' => true]);
                logger()->info("ResizeImage: DATABASE AGGIORNATO (is_processed = true)");
            }

        } catch (\Exception $e) {
            logger()->error("ResizeImage CRASH COMPLETO: " . $e->getMessage());
        }
    }
}

