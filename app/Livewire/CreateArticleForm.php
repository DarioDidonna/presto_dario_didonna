<?php

namespace App\Livewire;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;

class CreateArticleForm extends Component
{

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

        session()->flash('message', 'Annuncio pubblicato con successo nel sistema!');

        $this->reset(['title', 'price', 'description', 'category']);
    }

    public function render()
    {
        return view('livewire.create-article-form');
    }
}
