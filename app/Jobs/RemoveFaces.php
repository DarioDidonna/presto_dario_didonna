<?php

namespace App\Jobs;

use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RemoveFaces implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Definiamo le proprietà per accettare l'ID in qualunque modo sia passato
    public $id;
    public $article_image_id;

    /**
     * Create a new job instance.
     */
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
            // Intercettiamo l'ID corretto
            $imageId = $this->id ?? $this->article_image_id;
            
            $i = \App\Models\Image::find($imageId);
            if (!$i) return;

            $srcPath = storage_path('app/public/' . $i->path);
            if (!file_exists($srcPath)) return;

            $jsonPath = base_path('google_credential.json');
            if (!file_exists($jsonPath)) return;

            // 1. Inizializziamo il client di Google Vision
            $imageAnnotatorClient = new \Google\Cloud\Vision\V1\Client\ImageAnnotatorClient([
                'credentials' => $jsonPath
            ]);

            $image_content = file_get_contents($srcPath);
            
            // 2. Prepariamo i parametri della richiesta per i Volti
            $feature = (new \Google\Cloud\Vision\V1\Feature())
                ->setType(\Google\Cloud\Vision\V1\Feature\Type::FACE_DETECTION);
                
            $image = (new \Google\Cloud\Vision\V1\Image())
                ->setContent($image_content);
                
            $request = (new \Google\Cloud\Vision\V1\AnnotateImageRequest())
                ->setImage($image)
                ->setFeatures([$feature]);

            // 3. Impacchettiamo la richiesta dentro la BatchRequest (Risolve l'errore di tipo)
            $batchRequest = (new \Google\Cloud\Vision\V1\BatchAnnotateImagesRequest())
                ->setRequests([$request]);

            $response = $imageAnnotatorClient->batchAnnotateImages($batchRequest);
            $responses = $response->getResponses();
            
            // 4. Se Google ha trovato dei volti, passiamo al disegno del rettangolo nero
            if (count($responses) > 0 && $responses[0]->getFaceAnnotations()) {
                $faces = $responses[0]->getFaceAnnotations();

                $mime = mime_content_type($srcPath);
                $imageGD = null;

                if ($mime === 'image/jpeg' || $mime === 'image/jpg') {
                    $imageGD = imagecreatefromjpeg($srcPath);
                } elseif ($mime === 'image/png') {
                    $imageGD = imagecreatefrompng($srcPath);
                }

                if ($imageGD) {
                    foreach ($faces as $face) {
                        $vertices = $face->getBoundingPoly()->getVertices();
                        
                        $x1 = $vertices[0]->getX();
                        $y1 = $vertices[0]->getY();
                        $x2 = $vertices[2]->getX();
                        $y2 = $vertices[2]->getY();

                        // Colore Nero della censura
                        $black = imagecolorallocate($imageGD, 0, 0, 0);
                        
                        // Disegna il rettangolo pieno sopra il volto
                        imagefilledrectangle($imageGD, $x1, $y1, $x2, $y2, $black);
                    }

                    // Salva sovrascrivendo il file originale sul Mac
                    if ($mime === 'image/jpeg' || $mime === 'image/jpg') {
                        imagejpeg($imageGD, $srcPath, 95);
                    } elseif ($mime === 'image/png') {
                        imagepng($imageGD, $srcPath);
                    }
                    
                    imagedestroy($imageGD);
                }
            }

            $imageAnnotatorClient->close();

        } catch (\Exception $e) {
            logger()->error("RemoveFaces CRASH DEFINITIVO: " . $e->getMessage());
        }
    }
}