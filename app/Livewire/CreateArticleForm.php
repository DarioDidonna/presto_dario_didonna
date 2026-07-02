<?php

namespace App\Livewire;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateArticleForm extends Component
{
    use WithFileUploads;

    public $images = [];
    
    public $temporary_images = []; 

    #[Validate('required', message: 'Il titolo dell\'annuncio è obbligatorio.')]
    #[Validate('min:5', message: 'Il titolo deve contenere almeno 5 caratteri.')]
    public $title;

    #[Validate('required', message: 'Il prezzo è obbligatorio.')]
    #[Validate('numeric', message: 'Il prezzo deve essere un numero.')]
    #[Validate('min:0.01', message: 'Il prezzo deve essere maggiore di zero.')]
    public $price;

    #[Validate('required', message: 'La descrizione è obbligatoria.')]
    #[Validate('min:15', message: 'La descrizione deve contenere almeno 15 caratteri.')]
    public $description;

    #[Validate('required', message: 'Selezionare una categoria è obbligatorio.')]
    public $category;

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

        if (count($this->images) > 0) {
            foreach ($this->images as $image) {
                $this->article->images()->create([
                    'path' => $image->store('articles', 'public')
                ]);
            }
        }

        session()->flash('message', 'Annuncio pubblicato con successo!');

        $this->reset(['title', 'price', 'description', 'category', 'images', 'temporary_images']);
    }

    public function updatedTemporaryImages()
    {
        $this->validate([
            'temporary_images.*' => 'image|max:5120', 
        ]);

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

    public function render()
    {
        return view('livewire.create-article-form');
    }
}