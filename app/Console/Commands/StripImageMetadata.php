<?php

namespace App\Console\Commands;

use App\Models\Image;
use App\Support\PhotoMetadata;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

#[Signature('app:strip-image-metadata')]
#[Description('Toglie i metadati (GPS compreso) dalle foto degli annunci caricate prima della pulizia automatica')]
class StripImageMetadata extends Command
{
    public function handle()
    {
        $disk = Storage::disk('public');
        $cleaned = 0;

        Image::query()->each(function (Image $image) use ($disk, &$cleaned) {
            if (! $disk->exists($image->path)) {
                return;
            }

            if (PhotoMetadata::strip($disk->path($image->path))) {
                $cleaned++;
            } else {
                $this->warn("Immagine {$image->id} non ripulita: formato non leggibile ({$image->path})");
            }
        });

        $this->info("Foto ripulite: {$cleaned}");

        return self::SUCCESS;
    }
}
