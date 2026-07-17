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
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;

class RemoveFaces implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $article_image_id;

    public function __construct($article_image_id)
    {
        $this->article_image_id = $article_image_id;
    }

    public function handle(): void
    {
        try {
            $i = Image::find($this->article_image_id);
            if (!$i) {
                logger()->error("RemoveFaces: Modello Image non trovato per ID: " . $this->article_image_id);
                return;
            }

            logger()->info("RemoveFaces [ID {$this->article_image_id}]: Il path nel DB è: '" . $i->path . "'");

            $srcPath = storage_path('app/public/' . $i->path);
            logger()->info("RemoveFaces [ID {$this->article_image_id}]: Cerco il file in: " . $srcPath);

            if (!file_exists($srcPath)) {
                logger()->error("RemoveFaces [ID {$this->article_image_id}]: File originale NON TROVATO su disco!");
                return;
            }

            $jsonPath = base_path('google_credential.json');
            if (!file_exists($jsonPath)) {
                logger()->error("RemoveFaces: Manca il file google_credential.json nella root del progetto.");
                return;
            }

            $googleVisionClient = new ImageAnnotatorClient([
                'credentials' => $jsonPath
            ]);

            $image_content = file_get_contents($srcPath);
            $google_image = new VisionImage(['content' => $image_content]);
            
            $googleFeature = new Feature();
            $googleFeature->setType(Type::FACE_DETECTION);

            $request = new AnnotateImageRequest();
            $request->setImage($google_image)->setFeatures([$googleFeature]);

            $batchRequest = new BatchAnnotateImagesRequest();
            $batchRequest->setRequests([$request]);

            $responseBatch = $googleVisionClient->batchAnnotateImages($batchRequest);
            $responses = $responseBatch->getResponses();
            
            if (empty($responses)) {
                logger()->error("RemoveFaces: Risposta vuota da Google Vision.");
                $googleVisionClient->close();
                return;
            }

            $response = $responses[0];
            $faces = $response->getFaceAnnotations();

            if ($response->getError()) {
                logger()->error("RemoveFaces ERRORE API GOOGLE: " . $response->getError()->getMessage());
                $googleVisionClient->close();
                return;
            }

            logger()->info("RemoveFaces: Google Vision ha risposto! Volti rilevati: " . count($faces));

            if (count($faces) > 0) {
                $isPng = str_ends_with(strtolower($srcPath), '.png');
                $gdImage = $isPng ? @imagecreatefrompng($srcPath) : @imagecreatefromjpeg($srcPath);

                if (!$gdImage) {
                    logger()->error("RemoveFaces: Impossibile caricare la risorsa GD.");
                    $googleVisionClient->close();
                    return;
                }

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

                        imagefilledrectangle($gdImage, $minX, $minY, $maxX, $maxY, $black);
                    }
                }

                if ($isPng) {
                    imagepng($gdImage, $srcPath);
                } else {
                    imagejpeg($gdImage, $srcPath, 90);
                }

                imagedestroy($gdImage);
                logger()->info("RemoveFaces [ID {$this->article_image_id}]: Volti censurati e file salvato con successo.");
            } else {
                logger()->warning("RemoveFaces [ID {$this->article_image_id}]: Nessun volto rilevato nella foto.");
            }

            $googleVisionClient->close();
        } catch (\Exception $e) {
            logger()->error("REMOVE FACES CRASH TOTALE: " . $e->getMessage());
        }
    }
}