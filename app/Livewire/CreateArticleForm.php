<?php

namespace App\Livewire;

use App\Livewire\Concerns\HandlesArticleForm;
use App\Models\Article;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateArticleForm extends Component
{
    use WithFileUploads, HandlesArticleForm;

    public $article;

    public function store()
    {
        $this->validate();

        $this->article = Article::create([
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'category_id' => $this->category,
            'user_id' => Auth::id()
        ]);

        $this->storeNewImages($this->article);

        session()->flash('message', __('ui.article_published'));
        $this->reset(['title', 'price', 'description', 'category', 'images', 'temporary_images']);
    }

    public function render()
    {
        return view('livewire.article-form', [
            'submitAction' => 'store',
            'submitLabel' => __('ui.publish_ad'),
            'submitIcon' => 'bi-plus-circle',
            'existingImages' => collect(),
        ]);
    }
}
