<?php

namespace App\Http\Requests;

use App\Models\Review;
use Illuminate\Foundation\Http\FormRequest;

// L'autorizzazione è sulla rotta (can:review,purchaseRequest)
class StoreReviewRequest extends FormRequest
{
    /**
     * Un modulo per ogni ordine nella stessa pagina: gli errori finiscono in un "bag" per richiesta,
     * così compaiono solo sotto il modulo inviato.
     */
    protected function prepareForValidation(): void
    {
        $this->errorBag = 'review'.$this->route('purchaseRequest')->id;
        $this->merge(['comment' => trim((string) $this->input('comment')) ?: null]);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'between:1,'.Review::MAX_RATING],
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'rating.required' => __('ui.reviews_rating_required'),
            'rating.integer' => __('ui.reviews_rating_required'),
            'rating.between' => __('ui.reviews_rating_required'),
        ];
    }
}
