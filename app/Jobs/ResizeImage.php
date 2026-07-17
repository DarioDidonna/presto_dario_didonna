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
            $image = \Spatie\Image\Image::useImageDriver(\Spatie\Image\Enums\ImageDriver::Gd)
                ->load($srcPath)
                ->fit(
                    \Spatie\Image\Enums\Fit::Crop, 
                    $w, 
                    $h
                );

            $watermarkPath = base_path('resources/img/watermark.png');
            
            if (file_exists($watermarkPath)) {
                $image->watermark(
                    $watermarkPath,
                    position: \Spatie\Image\Enums\AlignPosition::TopRight,
                    width: 50,
                    height: 50,
                    paddingX: 10, 
                    paddingY: 10, 
                    paddingUnit: \Spatie\Image\Enums\Unit::Pixel
                );
                logger()->info("ResizeImage: Watermark TopRight applicato in memoria.");
            } else {
                logger()->warning("ResizeImage: FILE WATERMARK MANCANTE in " . $watermarkPath);
            }

            if (file_exists($destPath)) {
                @unlink($destPath);
            }

            $image->save($destPath);
            logger()->info("ResizeImage: Miniatura salvata fisicamente in: " . $destPath);

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

