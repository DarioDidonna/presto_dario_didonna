<?php

namespace App\Jobs;

use App\Models\Image;
use App\Services\AgentUnavailableException;
use App\Services\AiAgentClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

/**
 * Primo anello della catena di elaborazione: raddrizza la foto e ne oscura i volti con il rilevatore locale del
 * servizio Python (YuNet). Salva in faces_count quanti volti ha oscurato, per il revisore.
 *
 * Se il servizio è spento ritenta più tardi; dopo l'ultimo tentativo la catena prosegue comunque (miniature),
 * con faces_count vuoto: la foto non è pubblica finché un revisore, avvisato, non l'approva.
 */
class RemoveFaces implements ShouldQueue
{
    use Queueable;

    public int $tries = 4;

    public function __construct(public int $imageId) {}

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [30, 120, 600];
    }

    public function handle(AiAgentClient $agent): void
    {
        $image = Image::find($this->imageId);
        $path = $image ? Storage::disk('public')->path($image->path) : null;
        if (! $image || ! is_file($path)) {
            logger()->warning("RemoveFaces: immagine {$this->imageId} non trovata");

            return;
        }

        $this->straighten($path);

        if (! $agent->isEnabled()) {
            logger()->warning("RemoveFaces [{$this->imageId}]: servizio AI non configurato, volti non controllati");

            return;
        }

        try {
            $result = $agent->faces($path);
        } catch (AgentUnavailableException $error) {
            if ($this->attempts() < $this->tries) {
                $this->release($this->backoff()[$this->attempts() - 1] ?? 600);

                return;
            }
            report($error);

            return;
        }

        $image->faces_count = $this->blackOut($path, $result);
        $image->save();
    }

    /**
     * Applica la rotazione EXIF ai pixel e salva la foto dritta, senza metadati: il rilevatore e GD lavorano così
     * sulla stessa immagine (prima le foto del telefono "di lato" avevano i riquadri nel punto sbagliato).
     */
    private function straighten(string $path): void
    {
        $orientation = function_exists('exif_read_data') && @getimagesize($path)[2] === IMAGETYPE_JPEG
            ? (int) (@exif_read_data($path)['Orientation'] ?? 1)
            : 1;
        if ($orientation <= 1 || $orientation > 8) {
            return;
        }

        $gd = @imagecreatefromstring((string) file_get_contents($path));
        if (! $gd) {
            return;
        }
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
        if ($angle) {
            $gd = imagerotate($gd, $angle, 0);
        }
        imagejpeg($gd, $path, 92);
    }

    /**
     * Riempie di nero i riquadri ricevuti (già allargati dal servizio) e salva la foto.
     *
     * @param  array{width: int, height: int, faces: list<array{x: int, y: int, width: int, height: int}>}  $result
     */
    private function blackOut(string $path, array $result): int
    {
        if ($result['faces'] === []) {
            return 0;
        }

        $type = (@getimagesize($path) ?: [])[2] ?? null;
        $gd = @imagecreatefromstring((string) file_get_contents($path));
        if (! $gd) {
            return 0;
        }

        // Stessa griglia di pixel attesa; se per qualunque motivo differisce, i riquadri vengono riscalati
        $scaleX = imagesx($gd) / max(1, $result['width']);
        $scaleY = imagesy($gd) / max(1, $result['height']);
        $black = imagecolorallocate($gd, 0, 0, 0);

        foreach ($result['faces'] as $face) {
            imagefilledrectangle(
                $gd,
                (int) floor($face['x'] * $scaleX),
                (int) floor($face['y'] * $scaleY),
                (int) ceil(($face['x'] + $face['width']) * $scaleX),
                (int) ceil(($face['y'] + $face['height']) * $scaleY),
                $black,
            );
        }

        match ($type) {
            IMAGETYPE_PNG => imagepng($gd, $path),
            IMAGETYPE_WEBP => imagewebp($gd, $path, 90),
            default => imagejpeg($gd, $path, 90),
        };

        return count($result['faces']);
    }
}
