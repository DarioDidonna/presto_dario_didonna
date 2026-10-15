<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchArticlesTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_shows_only_accepted_articles(): void
    {
        Article::factory()->accepted()->create(['title' => 'Moto d\'epoca perfetta', 'description' => 'Descrizione di prova']);
        Article::factory()->rejected()->create(['title' => 'Moto d\'epoca perfetta', 'description' => 'Descrizione da non mostrare']);
        Article::factory()->create(['title' => 'Moto in revisione', 'description' => 'Descrizione in attesa']);

        $response = $this->get('/search/article?query=Moto');

        $response->assertOk();
        $response->assertViewHas('articles', fn ($articles) => $articles->count() === 1);
        $response->assertSee('Moto d&#039;epoca perfetta', false);
        $response->assertDontSee('Descrizione da non mostrare', false);
        $response->assertDontSee('Moto in revisione', false);
    }

    public function test_empty_or_invalid_search_redirects_to_index(): void
    {
        // Con TNTSearch queste richieste causavano un errore 500
        $this->get('/search/article')->assertRedirect(route('article.index'));
        $this->get('/search/article?query=%20%20')->assertRedirect(route('article.index'));
        $this->get('/search/article?query[]=x')->assertRedirect(route('article.index'));
    }
}
