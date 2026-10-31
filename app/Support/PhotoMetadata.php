<?php

namespace App\Support;

/**
 * Toglie dalle foto i metadati (posizione GPS, modello del telefono, data dello scatto) ricodificandole con GD,
 * che non li riscrive. La rotazione EXIF, che andrebbe persa insieme a loro, viene prima applicata ai pixel.
 */
class PhotoMetadata
{
    /**
     * @return bool false se il file non è un'immagine che GD sa leggere e riscrivere: va scartato
     */
    public static function strip(string $path): bool
    {
        $type = (@getimagesize($path) ?: [])[2] ?? null;
        $gd = @imagecreatefromstring((string) file_get_contents($path));
        if (! $gd) {
            return false;
        }

        if ($type === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
            $gd = self::upright($gd, (int) (@exif_read_data($path)['Orientation'] ?? 1));
        }

        // I PNG e i WebP trasparenti non devono diventare neri
        imagealphablending($gd, false);
        imagesavealpha($gd, true);

        return match ($type) {
            IMAGETYPE_JPEG => imagejpeg($gd, $path, 92),
            IMAGETYPE_PNG => imagepng($gd, $path),
            IMAGETYPE_WEBP => imagewebp($gd, $path, 90),
            IMAGETYPE_GIF => imagegif($gd, $path),
            default => false,
        };
    }

    private static function upright(\GdImage $gd, int $orientation): \GdImage
    {
        // Le orientazioni specchiate (2, 4, 5, 7) = specchio orizzontale + la rotazione qui sotto (4: 180° = verticale)
        if (in_array($orientation, [2, 4, 5, 7], true)) {
            imageflip($gd, IMG_FLIP_HORIZONTAL);
        }
        // imagerotate gira in senso antiorario: 270 = 90° in senso orario. 5 e 7 sono specchiate e poi ruotate
        $angle = match ($orientation) {
            3, 4 => 180,
            6, 7 => 270,
            5, 8 => 90,
            default => 0,
        };

        return $angle ? imagerotate($gd, $angle, 0) : $gd;
    }
}
