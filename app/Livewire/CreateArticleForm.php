<?php

namespace App\Livewire;

use App\Livewire\Concerns\HandlesArticleForm;
use App\Models\Article;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateArticleForm extends Component
{
    use HandlesArticleForm, WithFileUploads;

    public $article;

    public function store()
    {
        $this->validateArticle();

        $this->article = Article::create([...$this->form->attributes(), 'user_id' => Auth::id()]);

        $this->storeNewImages($this->article);

        session()->flash('message', __('ui.article_published'));
        $this->form->reset();
        $this->reset(['images', 'temporary_images']);
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
