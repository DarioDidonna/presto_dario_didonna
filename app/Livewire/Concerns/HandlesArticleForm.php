<?php

namespace App\Livewire\Concerns;

use App\Models\Article;
use Illuminate\Validation\ValidationException;

/**
 * Campi, validazione e gestione delle nuove foto condivisi da creazione e modifica annuncio.
 */
trait HandlesArticleForm
{
    public const MAX_IMAGES = 6;

    public $images = [];
    public $temporary_images = [];

    public $title;
    public $price;
    public $description;
    public $category;

    // Foto già salvate che restano sull'annuncio (0 in creazione)
    protected function keptImagesCount(): int
    {
        return 0;
    }

    protected function maxNewImages(): int
    {
        return max(0, self::MAX_IMAGES - $this->keptImagesCount());
    }

    protected function rules(): array
    {
        return [
            'title' => 'required|string|min:5|max:100',
            'price' => 'required|numeric|min:0.01|max:999999.99',
            'description' => 'required|string|min:15|max:5000',
            'category' => 'required|integer|exists:categories,id',
            'images' => 'array|max:' . $this->maxNewImages(),
            'images.*' => 'image|max:5120',
        ];
    }

    protected function messages(): array
    {
        $maxImages = __('ui.validation_images_max_count', ['max' => self::MAX_IMAGES]);

        return [
            'title.required' => __('ui.validation_title_required'),
            'title.min' => __('ui.validation_title_min'),
            'title.max' => __('ui.validation_title_max'),
            'price.required' => __('ui.validation_price_required'),
            'price.numeric' => __('ui.validation_price_numeric'),
            'price.min' => __('ui.validation_price_min'),
            'price.max' => __('ui.validation_price_max'),
            'description.required' => __('ui.validation_description_required'),
            'description.min' => __('ui.validation_description_min'),
            'description.max' => __('ui.validation_description_max'),
            'category.required' => __('ui.validation_category_required'),
            'category.integer' => __('ui.validation_category_exists'),
            'category.exists' => __('ui.validation_category_exists'),
            'images.max' => $maxImages,
            'images.*.image' => __('ui.validation_image_image'),
            'images.*.max' => __('ui.validation_image_max'),
            'temporary_images.*.image' => __('ui.validation_image_image'),
            'temporary_images.*.max' => __('ui.validation_image_max'),
            'temporary_images.max' => $maxImages,
        ];
    }

    // Validazione in tempo reale dei campi testuali
    public function updated($property)
    {
        if (in_array($property, ['title', 'price', 'description', 'category'], true)) {
            $this->validateOnly($property);
        }
    }

    public function updatedTemporaryImages()
    {
        // Senza questo, l'errore di un caricamento rifiutato restava visibile anche dopo uno valido
        $this->resetErrorBag(['temporary_images', 'temporary_images.*', 'images', 'images.*']);

        try {
            $this->validate([
                'temporary_images' => 'array|max:' . ($this->maxNewImages() - count($this->images)),
                'temporary_images.*' => 'image|max:5120',
            ]);
        } catch (ValidationException $e) {
            // I file scartati non devono restare in coda: si sommerebbero al caricamento successivo
            $this->temporary_images = [];
            throw $e;
        }

        foreach ($this->temporary_images as $image) {
            $this->images[] = $image;
        }

        $this->temporary_images = [];
    }

    public function removeImage($key)
    {
        if (in_array($key, array_keys($this->images))) {
            unset($this->images[$key]);
            $this->images = array_values($this->images);
        }
    }

    // Salva le nuove foto e avvia la catena di elaborazione (volti, miniature, Google Vision)
    protected function storeNewImages(Article $article): void
    {
        foreach ($this->images as $image) {
            $article->images()
                ->create(['path' => $image->store("articles/{$article->id}", 'public')])
                ->process();
        }
    }
}
