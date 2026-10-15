<?php

namespace App\Jobs;

use App\Jobs\Concerns\CallsGoogleVision;
use App\Models\Image;
use Google\Cloud\Vision\V1\Feature\Type;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GoogleVisionLabelImage implements ShouldQueue
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
                logger()->error("GoogleVisionLabelImage: Modello Image non trovato per ID: " . $this->article_image_id);
                return;
            }

            $response = $this->annotate($i, Type::LABEL_DETECTION);
            if (!$response) {
                return;
            }

            $result = [];
            foreach ($response->getLabelAnnotations() as $label) {
                $result[] = $label->getDescription();
            }

            $i->labels = $result;
            $i->save();

            logger()->info("GoogleVisionLabelImage: Etichette salvate con successo per l'immagine ID: " . $this->article_image_id);
        } catch (\Throwable $e) {
            // Non blocchiamo la catena: un errore di Google Vision non deve impedire la pubblicazione
            logger()->error("GoogleVisionLabelImage CRASH: " . $e->getMessage());
        }
    }
}
