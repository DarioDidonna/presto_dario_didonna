<?php

namespace Tests\Feature;

use App\Jobs\GoogleVisionLabelImage;
use App\Jobs\GoogleVisionSafeSearch;
use App\Jobs\RemoveFaces;
use App\Jobs\ResizeImage;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArticleFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_by_category_shows_only_accepted_articles_paginated(): void
    {
        $category = Category::factory()->create();
        Article::factory()->count(13)->accepted()->for($category)->create();
        Article::factory()->for($category)->create();
        Article::factory()->rejected()->for($category)->create();
        Article::factory()->accepted()->create(); // altra categoria

        $response = $this->get(route('byCategory', $category))->assertOk();
        $this->assertCount(12, $response->viewData('articles'));
        $this->assertSame(13, $response->viewData('articles')->total());
    }

    public function test_revisor_carousel_shows_every_image(): void
    {
        $article = Article::factory()->create();
        $article->images()->create(['path' => 'articles/1/a.jpg']);
        $article->images()->create(['path' => 'articles/1/b.jpg']);

        $this->actingAs(User::factory()->revisor()->create())->get(route('revisor.index'))
            ->assertOk()
            ->assertSee('crop_300x300_a.jpg')
            ->assertSee('crop_300x300_b.jpg');
    }

    public function test_job_chain_creates_thumbnail_even_without_google_credentials(): void
    {
        config(['services.google_vision.credentials' => '/percorso/inesistente.json']);
        Storage::fake('public');

        $article = Article::factory()->create();
        $path = UploadedFile::fake()->image('foto.jpg', 800, 600)->store("articles/{$article->id}", 'public');
        $image = $article->images()->create(['path' => $path]);

        RemoveFaces::withChain([
            new ResizeImage($image->path, 300, 300),
            new ResizeImage($image->path, \App\Models\Image::DISPLAY_SIZE, \App\Models\Image::DISPLAY_SIZE),
            new GoogleVisionSafeSearch($image->id),
            new GoogleVisionLabelImage($image->id),
        ])->dispatch($image->id);

        Storage::disk('public')->assertExists(dirname($path) . '/crop_300x300_' . basename($path));
        Storage::disk('public')->assertExists(dirname($path) . '/crop_800x800_' . basename($path));
        $this->assertStringEndsWith('crop_800x800_' . basename($path), $image->getDisplayUrl());
    }
}
