<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\Image;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DemoSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_demo_seeder_creates_users_articles_and_images(): void
    {
        $this->seed(DemoSeeder::class);

        $this->assertSame(10, Category::count());
        $this->assertSame(5, User::count());
        $this->assertTrue((bool) User::where('email', 'revisore@presto.it')->value('is_revisor'));
        $this->assertFalse((bool) User::where('email', 'demo@presto.it')->value('is_revisor'));

        $this->assertSame(24, Article::count());
        $this->assertGreaterThan(0, Article::whereNull('is_accepted')->count());
        $this->assertGreaterThan(0, Article::where('is_accepted', false)->count());
        $this->assertGreaterThan(15, Article::where('is_accepted', true)->count());
        $this->assertGreaterThan(0, Article::where('created_at', '>=', now()->subWeek())->count());

        // Ogni immagine ha l'originale e le due versioni con watermark
        $this->assertGreaterThanOrEqual(24, Image::count());
        foreach (Image::all() as $image) {
            $dir = dirname($image->path);
            $name = basename($image->path);
            Storage::disk('public')->assertExists([$image->path, "$dir/crop_300x300_$name", "$dir/crop_800x800_$name"]);
        }
        [$w, $h] = getimagesize(Storage::disk('public')->path(Image::first()->path));
        $this->assertSame([1200, 900], [$w, $h]);

        // Le pagine del sito popolato funzionano e la ricerca trova gli annunci demo
        $article = Article::accepted()->first();
        foreach (['/', '/article/index', "/category/{$article->category_id}", "/show/article/{$article->id}"] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/search/article?query=bicicletta')->assertOk()->assertSee('Bicicletta da corsa');

        // Le credenziali demo funzionano
        $this->post('/login', ['email' => 'revisore@presto.it', 'password' => DemoSeeder::PASSWORD]);
        $this->assertAuthenticatedAs(User::where('email', 'revisore@presto.it')->first());
        $this->get(route('revisor.index'))->assertOk();
    }

    public function test_seeder_can_run_twice_without_duplicates(): void
    {
        $this->seed(DemoSeeder::class);
        $this->seed(DemoSeeder::class);

        $this->assertSame(10, Category::count());
        $this->assertSame(5, User::count());
        $this->assertSame(24, Article::count());
    }

}
