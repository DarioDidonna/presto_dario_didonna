<?php

namespace App\Livewire\Forms;

use App\Models\Article;
use Livewire\Form;

/**
 * Campi testuali dell'annuncio con regole e messaggi: l'equivalente Livewire di una Form Request,
 * condiviso da creazione e modifica. Le foto restano nel componente (il limite dipende da quelle già salvate).
 */
class ArticleForm extends Form
{
    public $title;

    public $price;

    public $description;

    public $category;

    protected function rules(): array
    {
        return [
            'title' => 'required|string|min:5|max:100',
            'price' => 'required|numeric|min:0.01|max:999999.99',
            'description' => 'required|string|min:15|max:5000',
            'category' => 'required|integer|exists:categories,id',
        ];
    }

    protected function messages(): array
    {
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
        ];
    }

    // Regole e messaggi con il prefisso "form.", per validarli dal componente insieme alle foto
    public function prefixedRules(): array
    {
        return collect($this->rules())->mapWithKeys(fn ($rule, $key) => ["form.{$key}" => $rule])->all();
    }

    public function prefixedMessages(): array
    {
        return collect($this->messages())->mapWithKeys(fn ($message, $key) => ["form.{$key}" => $message])->all();
    }

    public function fillFromArticle(Article $article): void
    {
        $this->title = $article->title;
        $this->price = $article->price;
        $this->description = $article->description;
        $this->category = $article->category_id;
    }

    // Colonne della tabella articles corrispondenti ai campi del form
    public function attributes(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'category_id' => $this->category,
        ];
    }
}
