<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Spatie\Image\Enums\AlignPosition;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Enums\ImageDriver;
use Spatie\Image\Enums\Unit;
use Spatie\Image\Image;

class ResizeImage implements ShouldQueue
{
    use Queueable;

    private $w;
    private $h;
    private $fileName;
    private $path;
    private $fullPathForDb;

    public function __construct($filePath, $w, $h)
    {
        $this->fullPathForDb = $filePath;
        $this->path = dirname($filePath);
        $this->fileName = basename($filePath);
        $this->w = $w;
        $this->h = $h;
    }

    public function handle(): void
    {
        $w = $this->w;
        $h = $this->h;

        $srcPath = storage_path('app/public/' . $this->path . '/' . $this->fileName);
        $destPath = storage_path('app/public/' . $this->path . "/crop_{$w}x{$h}_" . $this->fileName);

        if (!file_exists($srcPath)) {
            logger()->error("ResizeImage: file non trovato in {$srcPath}");
            return;
        }

        try {
            if (file_exists($destPath)) {
                @unlink($destPath);
            }

            $watermarkPath = base_path('resources/img/watermark.png');

            $image = Image::useImageDriver(ImageDriver::Gd)
                ->load($srcPath)
                ->fit(Fit::Crop, $w, $h);

            if (file_exists($watermarkPath)) {
                $image->watermark(
                    $watermarkPath,
                    position: AlignPosition::TopRight,
                    paddingX: 10,
                    paddingY: 10,
                    paddingUnit: Unit::Pixel,
                    width: 60,
                    widthUnit: Unit::Pixel,
                    height: 60,
                    heightUnit: Unit::Pixel,
                    alpha: 100
                );


                logger()->info("ResizeImage: watermark applicata in alto a destra.");
            } else {
                logger()->warning("ResizeImage: watermark non trovata in {$watermarkPath}");
            }

            $image->save($destPath);

            logger()->info("ResizeImage: immagine salvata in {$destPath}");

            $pureFileName = $this->fileName;
            $imageModel = \App\Models\Image::where('path', 'LIKE', '%' . $pureFileName)->first();

            if ($imageModel) {
                $imageModel->update(['is_processed' => true]);
                logger()->info("ResizeImage: database aggiornato.");
            }
        } catch (\Exception $e) {
            logger()->error("ResizeImage CRASH: " . $e->getMessage());
        }
    }
}