<?php

namespace App\Jobs;

use App\Models\Image;
use Google\Cloud\Vision\V1\Feature;
use Google\Cloud\Vision\V1\Feature\Type;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Google\Cloud\Vision\V1\AnnotateImageRequest;
use Google\Cloud\Vision\V1\Image as VisionImage;
use Google\Cloud\Vision\V1\BatchAnnotateImagesRequest;
use Google\Cloud\Vision\V1\Client\ImageAnnotatorClient;

class GoogleVisionLabelImage implements ShouldQueue
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
                logger()->error("GoogleVisionLabelImage: Modello Image non trovato per ID: " . $this->article_image_id);
                return;
            }

            $srcPath = storage_path('app/public/' . $i->path);

            if (!file_exists($srcPath)) {
                logger()->warning("GoogleVisionLabelImage: File non trovato in " . $srcPath . ". Salto il controllo.");
                return;
            }

            $image = file_get_contents($srcPath);
            $jsonPath = base_path('google_credential.json');

            if (!file_exists($jsonPath)) {
                logger()->error("GoogleVisionLabelImage: Manca il file delle credenziali.");
                return;
            }

            $googleVisionClient = new ImageAnnotatorClient([
                'credentials' => $jsonPath
            ]);
            
            $google_image = new VisionImage([
                'content' => $image
            ]);

            $googleFeature = new Feature();
            $googleFeature->setType(Type::LABEL_DETECTION);

            $request = new AnnotateImageRequest();
            $request->setImage($google_image);
            $request->setFeatures([$googleFeature]);

            $batchRequest = new BatchAnnotateImagesRequest();
            $batchRequest->setRequests([$request]);

            $responseBatch = $googleVisionClient->batchAnnotateImages($batchRequest);
            
            $responses = $responseBatch->getResponses();
            if (!isset($responses[0])) {
                logger()->error("GoogleVisionLabelImage: Risposta vuota da Google Vision.");
                $googleVisionClient->close();
                return;
            }

            $response = $responses[0];
            $labels = $response->getLabelAnnotations();

            if ($labels) {
                $result = [];
                foreach ($labels as $label) {
                    $result[] = $label->getDescription();
                }
                
                $i->labels = $result;
                $i->save();
                
                logger()->info("GoogleVisionLabelImage: Etichette salvate con successo per l'immagine ID: " . $this->article_image_id);
            }
            
            $googleVisionClient->close();

        } catch (\Exception $e) {
            logger()->error("GoogleVisionLabelImage CRASH GENERALE: " . $e->getMessage());
        }
    }
}