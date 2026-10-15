<?php

namespace App\Jobs;

use App\Jobs\Concerns\CallsGoogleVision;
use App\Models\Image;
use Google\Cloud\Vision\V1\Feature\Type;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class RemoveFaces implements ShouldQueue
{
    use CallsGoogleVision, Queueable;

    private $article_image_id;

    public function __construct($article_image_id)
    {
        $this->article_image_id = $article_image_id;
    }

    public function handle(): void
    {
        try {
            $i = Image::find($this->article_image_id);
            if (! $i) {
                logger()->error('RemoveFaces: Modello Image non trovato per ID: '.$this->article_image_id);

                return;
            }

            $response = $this->annotate($i, Type::FACE_DETECTION);
            if (! $response) {
                return;
            }

            $faces = $response->getFaceAnnotations();
            if (count($faces) === 0) {
                logger()->info("RemoveFaces [ID {$this->article_image_id}]: Nessun volto rilevato nella foto.");

                return;
            }

            $srcPath = Storage::disk('public')->path($i->path);
            $type = @getimagesize($srcPath)[2] ?? null;
            // imagecreatefromstring gestisce jpeg, png, gif, webp… senza basarsi sull'estensione
            $gdImage = @imagecreatefromstring(file_get_contents($srcPath));

            if (! $gdImage) {
                logger()->error('RemoveFaces: Impossibile caricare la risorsa GD.');

                return;
            }

            $black = imagecolorallocate($gdImage, 0, 0, 0);
            $width = imagesx($gdImage);
            $height = imagesy($gdImage);

            foreach ($faces as $face) {
                $xs = [];
                $ys = [];
                foreach ($face->getBoundingPoly()->getVertices() as $vertex) {
                    $xs[] = $vertex->getX();
                    $ys[] = $vertex->getY();
                }

                if ($xs) {
                    imagefilledrectangle(
                        $gdImage,
                        max(0, min($xs)),
                        max(0, min($ys)),
                        min($width - 1, max($xs)),
                        min($height - 1, max($ys)),
                        $black
                    );
                }
            }

            $saved = match ($type) {
                IMAGETYPE_PNG => imagepng($gdImage, $srcPath),
                IMAGETYPE_GIF => imagegif($gdImage, $srcPath),
                IMAGETYPE_WEBP => imagewebp($gdImage, $srcPath, 90),
                default => imagejpeg($gdImage, $srcPath, 90),
            };

            if ($saved) {
                logger()->info("RemoveFaces [ID {$this->article_image_id}]: Volti censurati e file salvato con successo.");
            } else {
                logger()->error("RemoveFaces [ID {$this->article_image_id}]: Salvataggio del file fallito.");
            }
        } catch (\Throwable $e) {
            logger()->error('RemoveFaces CRASH: '.$e->getMessage());
        }
    }
}
