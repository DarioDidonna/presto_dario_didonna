<?php

namespace App\Jobs\Concerns;

use App\Models\Image;
use Google\Cloud\Vision\V1\AnnotateImageRequest;
use Google\Cloud\Vision\V1\AnnotateImageResponse;
use Google\Cloud\Vision\V1\BatchAnnotateImagesRequest;
use Google\Cloud\Vision\V1\Client\ImageAnnotatorClient;
use Google\Cloud\Vision\V1\Feature;
use Google\Cloud\Vision\V1\Image as VisionImage;
use Illuminate\Support\Facades\Storage;

trait CallsGoogleVision
{
    /**
     * Invia l'immagine a Google Vision con la feature richiesta.
     * Restituisce null (dopo aver loggato il motivo) se qualcosa non va.
     */
    protected function annotate(Image $image, int $featureType): ?AnnotateImageResponse
    {
        $jobName = class_basename(static::class);
        $srcPath = Storage::disk('public')->path($image->path);

        if (! is_file($srcPath)) {
            logger()->warning("{$jobName}: file non trovato in {$srcPath}");

            return null;
        }

        $credentials = config('services.google_vision.credentials');
        if (! $credentials || ! is_file($credentials)) {
            logger()->error("{$jobName}: file delle credenziali Google mancante ({$credentials})");

            return null;
        }

        $client = new ImageAnnotatorClient(['credentials' => $credentials]);

        try {
            $request = (new AnnotateImageRequest)
                ->setImage(new VisionImage(['content' => file_get_contents($srcPath)]))
                ->setFeatures([(new Feature)->setType($featureType)]);

            $responses = $client
                ->batchAnnotateImages((new BatchAnnotateImagesRequest)->setRequests([$request]))
                ->getResponses();
        } finally {
            $client->close();
        }

        if (! isset($responses[0])) {
            logger()->error("{$jobName}: risposta vuota da Google Vision");

            return null;
        }

        if ($responses[0]->getError()) {
            logger()->error("{$jobName}: errore API Google: ".$responses[0]->getError()->getMessage());

            return null;
        }

        return $responses[0];
    }
}
