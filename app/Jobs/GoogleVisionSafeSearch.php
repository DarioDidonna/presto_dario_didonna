<?php

namespace App\Jobs;

use App\Models\Image;
use Google\Cloud\Vision\V1\Feature;
use Google\Cloud\Vision\V1\Feature\Type;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Google\Cloud\Vision\V1\AnnotateImageRequest;
use Google\Cloud\Vision\V1\BatchAnnotateImagesRequest;
use Google\Cloud\Vision\V1\Client\ImageAnnotatorClient;
use Google\Cloud\Vision\V1\Image as VisionImage;

class GoogleVisionSafeSearch implements ShouldQueue
{
    use Queueable;

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

            $srcPath = storage_path('app/public/' . $i->path);

            // 1. CONTROLLO DI SICUREZZA: Evita il crash se il file non esiste fisicamente
            if (!file_exists($srcPath)) {
                logger()->warning("GoogleVisionSafeSearch: File non trovato in " . $srcPath . ". Salto il controllo.");
                return;
            }

            $image = file_get_contents($srcPath);
            $jsonPath = base_path('google_credential.json');

            if (!file_exists($jsonPath)) {
                logger()->error("GoogleVisionSafeSearch: Manca il file delle credenziali.");
                return;
            }

            $googleVisionClient = new ImageAnnotatorClient([
                'credentials' => $jsonPath
            ]);
            
            $google_image = new VisionImage([
                'content' => $image
            ]);

            $googleFeature = new Feature();
            $googleFeature->setType(Type::SAFE_SEARCH_DETECTION);

            $request = new AnnotateImageRequest();
            $request->setImage($google_image);
            $request->setFeatures([$googleFeature]);

            $batchRequest = new BatchAnnotateImagesRequest();
            $batchRequest->setRequests([$request]);

            $responseBatch = $googleVisionClient->batchAnnotateImages($batchRequest);
            $response = $responseBatch->getResponses();
            $googleVisionClient->close();

            if (!isset($response[0])) {
                logger()->error("GoogleVisionSafeSearch: Risposta vuota da Google Vision.");
                return;
            }

            $safeSearchAnnotation = $response[0]->getSafeSearchAnnotation();
            
            if (!$safeSearchAnnotation) {
                logger()->error("GoogleVisionSafeSearch: Nessuna annotazione SafeSearch trovata.");
                return;
            }

            // 2. RECUPERO DEI VALORI INTERI DALL'ENUM: Usiamo ->value() per mapparli sull'array
            $adult = (int) $safeSearchAnnotation->getAdult();
            $spoof = (int) $safeSearchAnnotation->getSpoof();
            $medical = (int) $safeSearchAnnotation->getMedical();
            $violence = (int) $safeSearchAnnotation->getViolence();
            $racy = (int) $safeSearchAnnotation->getRacy();

            $likeliHoodName = [
                'text-secondary bi bi-circle-fill',
                'text-success bi bi-check-circle-fill',
                'text-success bi bi-check-circle-fill',
                'text-warning bi bi-exclamation-circle-fill',
                'text-warning bi bi-exclamation-circle-fill',
                'text-danger bi bi-dash-circle-fill'
            ];

            // Aggiorniamo le proprietà sul database
            $i->adult = $likeliHoodName[$adult];
            $i->spoof = $likeliHoodName[$spoof];
            $i->medical = $likeliHoodName[$medical];
            $i->violence = $likeliHoodName[$violence];
            $i->racy = $likeliHoodName[$racy];
            
            $i->save();
            
            logger()->info("GoogleVisionSafeSearch completato con successo per l'immagine ID: " . $this->article_image_id);

        } catch (\Exception $e) {
            logger()->error("GoogleVisionSafeSearch CRASH: " . $e->getMessage());
        }
    }
}