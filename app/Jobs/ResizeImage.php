<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Spatie\Image\Enums\ImageDriver;
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

        $srcPath = storage_path('app/public/' . $this->path . '/' . $this->fileName);
        $destPath = storage_path('app/public/' . $this->path . "/crop_{$w}x{$h}_" . $this->fileName);

        if (!file_exists($srcPath)) {
            logger()->error("ResizeImage: File non trovato in " . $srcPath);
            return;
        }

        try {
            \Spatie\Image\Image::useImageDriver(\Spatie\Image\Enums\ImageDriver::Gd)
                ->load($srcPath)
                ->width($w)
                ->height($h)
                ->save($destPath);

            logger()->info("ResizeImage completato per la foto censurata: " . $this->fileName);
        } catch (\Exception $e) {
            logger()->error("ResizeImage CRASH: " . $e->getMessage());
        }
    }
}
