<?php

namespace App\Jobs;

use App\Models\Image;
use Google\Cloud\Vision\V1\AnnotateImageRequest;
use Google\Cloud\Vision\V1\BatchAnnotateImagesRequest;
use Google\Cloud\Vision\V1\Client\ImageAnnotatorClient;
use Google\Cloud\Vision\V1\Feature;
use Google\Cloud\Vision\V1\Feature\Type;
use Google\Cloud\Vision\V1\Image as VisionImage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RemoveFaces implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Supportiamo sia l'ID liscio che la proprietà esplicita passata da Livewire
    public $id;
    public $article_image_id;

    public function __construct($article_image_id)
    {
        $this->article_image_id = $article_image_id;
    }

    public function handle(): void
    {
        try {
            // Un piccolo micro-ritardo di sicurezza per dare tempo al Mac di completare la scrittura fisica del file
            usleep(500000); // 0.5 secondi

            $imageId = $this->id ?? $this->article_image_id;

            $i = Image::find($imageId);
            if (!$i) {
                logger()->error("RemoveFaces: Modello Image non trovato per ID: " . $imageId);
                return;
            }

            $srcPath = storage_path('app/public/' . $i->path);

            if (!file_exists($srcPath)) {
                logger()->error("RemoveFaces: File originale non trovato in: " . $srcPath);
                return;
            }

            // Forza i permessi di lettura/scrittura sul file nel Mac
            @chmod($srcPath, 0666);

            // 1. Leggiamo il file così com'è per Google Vision
            $image_content = file_get_contents($srcPath);
            $jsonPath = base_path('google_credential.json');
            if (!file_exists($jsonPath)) {
                logger()->error("RemoveFaces: Manca il file delle credenziali.");
                return;
            }

            $googleVisionClient = new ImageAnnotatorClient([
                'credentials' => $jsonPath
            ]);

            $google_image = new VisionImage(['content' => $image_content]);
            $googleFeature = new Feature();
            $googleFeature->setType(Type::FACE_DETECTION);

            $request = new AnnotateImageRequest();
            $request->setImage($google_image);
            $request->setFeatures([$googleFeature]);

            $batchRequest = new BatchAnnotateImagesRequest();
            $batchRequest->setRequests([$request]);

            $responseBatch = $googleVisionClient->batchAnnotateImages($batchRequest);
            $response = $responseBatch->getResponses()[0];
            $faces = $response->getFaceAnnotations();

            // Logghiamo quante facce vede l'API in questo esatto momento
            logger()->info("RemoveFaces: Google Vision restituisce " . count($faces) . " volti per l'immagine " . $imageId);

            if (count($faces) > 0) {
                // 2. Carichiamo l'immagine usando GD nativo di PHP in base all'estensione
                $isPng = str_ends_with(strtolower($srcPath), '.png');
                $gdImage = $isPng ? @imagecreatefrompng($srcPath) : @imagecreatefromjpeg($srcPath);

                if (!$gdImage) {
                    logger()->error("RemoveFaces: Impossibile inizializzare la risorsa GD per il file.");
                    $googleVisionClient->close();
                    return;
                }

                // Colore nero per la censura
                $black = imagecolorallocate($gdImage, 0, 0, 0);

                foreach ($faces as $face) {
                    $vertices = $face->getBoundingPoly()->getVertices();
                    $bounds = [];
                    foreach ($vertices as $vertex) {
                        $x = $vertex->getX();
                        $y = $vertex->getY();
                        if ($x !== null && $y !== null) {
                            $bounds[] = [$x, $y];
                        }
                    }

                    if (!empty($bounds)) {
                        $xCoordinates = array_column($bounds, 0);
                        $yCoordinates = array_column($bounds, 1);

                        $minX = max(0, min($xCoordinates));
                        $maxX = max($xCoordinates);
                        $minY = max(0, min($yCoordinates));
                        $maxY = max($yCoordinates);

                        // Disegniamo il rettangolo nero direttamente sopra ogni faccia trovata
                        imagefilledrectangle($gdImage, $minX, $minY, $maxX, $maxY, $black);
                    }
                }

                // 3. Sovrascriviamo in sicurezza il file sul disco del Mac
                if (file_exists($srcPath)) {
                    @unlink($srcPath);
                }

                if ($isPng) {
                    imagepng($gdImage, $srcPath);
                } else {
                    imagejpeg($gdImage, $srcPath, 95);
                }

                imagedestroy($gdImage);
                logger()->info("RemoveFaces: Censura applicata con successo tramite GD nativo.");
            } else {
                logger()->warning("RemoveFaces: Google Vision non ha rilevato volti in questa specifica esecuzione.");
            }

            $googleVisionClient->close();
        } catch (\Exception $e) {
            logger()->error("REMOVE FACES INTRAPPOLATO: " . $e->getMessage());
        }
    }
}