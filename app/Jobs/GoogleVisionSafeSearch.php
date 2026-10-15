<?php

namespace App\Jobs;

use App\Jobs\Concerns\CallsGoogleVision;
use App\Models\Image;
use Google\Cloud\Vision\V1\Feature\Type;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GoogleVisionSafeSearch implements ShouldQueue
{
    use Queueable, CallsGoogleVision;

    private $article_image_id;

    public function __construct($article_image_id)
    {
        $this->article_image_id = $article_image_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $i = Image::find($this->article_image_id);
            if (!$i) {
                logger()->error("GoogleVisionSafeSearch: Modello Image non trovato per ID: " . $this->article_image_id);
                return;
            }

            $response = $this->annotate($i, Type::SAFE_SEARCH_DETECTION);
            if (!$response) {
                return;
            }

            $safeSearchAnnotation = $response->getSafeSearchAnnotation();

            if (!$safeSearchAnnotation) {
                logger()->error("GoogleVisionSafeSearch: Nessuna annotazione SafeSearch per l'immagine ID: " . $this->article_image_id);
                return;
            }

            // Indici = valori dell'enum Likelihood (UNKNOWN, VERY_UNLIKELY, UNLIKELY, POSSIBLE, LIKELY, VERY_LIKELY)
            $likeliHoodName = [
                'text-secondary bi bi-circle-fill',
                'text-success bi bi-check-circle-fill',
                'text-success bi bi-check-circle-fill',
                'text-warning bi bi-exclamation-circle-fill',
                'text-warning bi bi-exclamation-circle-fill',
                'text-danger bi bi-dash-circle-fill'
            ];
            $toClass = fn ($value) => $likeliHoodName[(int) $value] ?? $likeliHoodName[0];

            $i->adult = $toClass($safeSearchAnnotation->getAdult());
            $i->spoof = $toClass($safeSearchAnnotation->getSpoof());
            $i->medical = $toClass($safeSearchAnnotation->getMedical());
            $i->violence = $toClass($safeSearchAnnotation->getViolence());
            $i->racy = $toClass($safeSearchAnnotation->getRacy());
            $i->save();

            logger()->info("GoogleVisionSafeSearch completato con successo per l'immagine ID: " . $this->article_image_id);
        } catch (\Throwable $e) {
            logger()->error("GoogleVisionSafeSearch CRASH: " . $e->getMessage());
        }
    }
}
