<?php

namespace Tests\Feature;

use App\Jobs\RemoveFaces;
use App\Livewire\CreateArticleForm;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class CreateArticleFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_must_exist(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(CreateArticleForm::class)
            ->set('title', 'Bicicletta')
            ->set('price', 100)
            ->set('description', 'Bicicletta da corsa in ottimo stato')
            ->set('category', 999)
            ->call('store')
            ->assertHasErrors(['category' => 'exists']);

        $this->assertSame(0, Article::count());
    }

    public function test_cannot_upload_more_than_max_images(): void
    {
        Storage::fake('public');
        $files = array_map(fn ($n) => UploadedFile::fake()->image("f$n.jpg"), range(1, CreateArticleForm::MAX_IMAGES + 1));

        Livewire::actingAs(User::factory()->create())
            ->test(CreateArticleForm::class)
            ->set('temporary_images', $files)
            ->assertHasErrors(['temporary_images' => 'max'])
            ->assertCount('images', 0);
    }

    public function test_non_image_upload_is_rejected(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(CreateArticleForm::class)
            ->set('temporary_images', [UploadedFile::fake()->create('virus.pdf', 10, 'application/pdf')])
            ->assertHasErrors(['temporary_images.0' => 'image']);
    }

    public function test_valid_article_is_stored_and_messages_are_translated(): void
    {
        Queue::fake();
        Storage::fake('public');
        $category = Category::factory()->create();

        app()->setLocale('en');
        Livewire::actingAs(User::factory()->create())
            ->test(CreateArticleForm::class)
            ->set('title', 'abc')
            ->assertSee('The title must be at least 5 characters.')
            ->set('title', 'Bicicletta')
            ->set('price', 100)
            ->set('description', 'Bicicletta da corsa in ottimo stato')
            ->set('category', $category->id)
            ->set('temporary_images', [UploadedFile::fake()->image('a.jpg')])
            ->call('store')
            ->assertHasNoErrors();

        $article = Article::first();
        $this->assertNotNull($article);
        $this->assertNull($article->is_accepted);
        $this->assertCount(1, $article->images);
        Queue::assertPushed(RemoveFaces::class);
    }

    public function test_english_locale_is_selectable_and_ukrainian_is_not(): void
    {
        $this->post('/lingua/uk')->assertNotFound();
        $this->post('/lingua/en')->assertRedirect();
        $this->get('/')->assertOk()->assertSee('lang="en"', false);
    }

    public function test_category_name_is_translated(): void
    {
        $category = Category::create(['name' => 'Casa e Giardinaggio']);
        app()->setLocale('en');
        $this->assertSame(__('ui.casa_e_giardinaggio'), $category->translated_name);
        $this->assertSame('Inventata', Category::create(['name' => 'Inventata'])->translated_name);
    }

    public function test_upload_error_disappears_after_a_valid_upload(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(CreateArticleForm::class)
            ->set('temporary_images', [UploadedFile::fake()->create('virus.pdf', 10, 'application/pdf')])
            ->assertHasErrors('temporary_images.0')
            ->set('temporary_images', [UploadedFile::fake()->image('ok.jpg')])
            ->assertHasNoErrors()
            ->assertCount('images', 1);
    }
}
