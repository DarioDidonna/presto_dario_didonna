<?php

namespace App\Http\Resources\Agent;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/**
 * Annuncio come lo vede l'agente: solo dati pubblici (niente email né altri dati personali del venditore).
 * Titolo e descrizione sono scritti dagli utenti: l'agente li tratta come dati, non come istruzioni.
 */
class ArticleResource extends JsonResource
{
    public bool $detailed = false;

    public function toArray(Request $request): array
    {
        $description = Str::squish($this->description);

        return [
            'id' => $this->id,
            'title' => $this->title,
            'price' => (float) $this->price,
            'category' => $this->category?->translated_name,
            'description' => $this->detailed ? Str::limit($description, 2000) : Str::limit($description, 200),
            'url' => route('article.show', $this->resource),
            'image_url' => $this->images->first() ? url($this->images->first()->getUrl(300, 300)) : null,
            'published_at' => $this->created_at?->toDateString(),
            $this->mergeWhen($this->detailed, fn () => [
                'images' => $this->images->map(fn ($image) => url($image->getDisplayUrl()))->values(),
                'seller' => $this->user?->name,
            ]),
        ];
    }

    public function detailed(): static
    {
        $this->detailed = true;

        return $this;
    }
}
