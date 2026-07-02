<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchArticlesTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_shows_only_accepted_articles(): void
    {
        $category = Category::create([
            'name' => 'Motori',
            'icon' => 'car-front',
        ]);

        $user = User::factory()->create();

        $acceptedArticle = new Article([
            'title' => 'Moto d\'epoca perfetta',
            'description' => 'Descrizione di prova',
            'price' => 2500,
            'category_id' => $category->id,
            'user_id' => $user->id,
        ]);
        $acceptedArticle->is_accepted = true;
        $acceptedArticle->save();

        $rejectedArticle = new Article([
            'title' => 'Moto d\'epoca perfetta',
            'description' => 'Descrizione da non mostrare',
            'price' => 1800,
            'category_id' => $category->id,
            'user_id' => $user->id,
        ]);
        $rejectedArticle->is_accepted = false;
        $rejectedArticle->save();

        $response = $this->get('/search/article?query=Moto');

        $response->assertOk();
        $response->assertViewHas('articles', function ($articles) {
            return $articles->count() === 1;
        });
        $response->assertSee('Moto d&#039;epoca perfetta', false);
        $response->assertDontSee('Descrizione da non mostrare', false);
    }
}
