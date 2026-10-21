<?php

namespace Tests\Feature;

use App\Livewire\ReviewAssistant;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Tests\TestCase;

class ReviewAssistantTest extends TestCase
{
    use RefreshDatabase;

    private User $revisor;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.ai_agent.url' => 'http://agent.test', 'services.ai_agent.token' => 'segreto']);
        $this->revisor = User::factory()->revisor()->create();
    }

    private function fakeReview(array $overrides = []): void
    {
        Http::fake(['agent.test/review' => Http::response(array_merge([
            'recommendation' => 'reject',
            'confidence' => 0.9,
            'reasons' => ['Prezzo assurdo', 'Pagamento fuori dal sito'],
            'flags' => [['code' => 'contact_details', 'blocking' => false]],
            'model_used' => true,
        ], $overrides))]);
    }

    private function pendingArticleWithImages(): Article
    {
        $article = Article::factory()->for(Category::create(['name' => 'Elettronica']))->create([
            'title' => 'iPhone nuovo',
            'description' => 'Pagamento con ricarica',
            'price' => 50,
        ]);
        $article->images()->create(['path' => 'a/1.jpg']);
        $article->images()->create(['path' => 'a/2.jpg']);

        return $article;
    }

    public function test_dashboard_shows_the_ai_panel_only_when_the_agent_is_configured(): void
    {
        Article::factory()->create();

        $this->actingAs($this->revisor)->get(route('revisor.index'))->assertSeeLivewire(ReviewAssistant::class);

        config(['services.ai_agent.token' => '']);
        $this->actingAs($this->revisor)->get(route('revisor.index'))->assertDontSeeLivewire(ReviewAssistant::class);
    }

    public function test_sends_article_data_to_the_agent(): void
    {
        $this->fakeReview();
        $article = $this->pendingArticleWithImages();
        app()->setLocale('es');

        Livewire::actingAs($this->revisor)->test(ReviewAssistant::class, ['articleId' => $article->id])->call('ask');

        Http::assertSent(fn (Request $request) => $request->url() === 'http://agent.test/review'
            && $request->hasHeader('Authorization', 'Bearer segreto')
            && $request['title'] === 'iPhone nuovo'
            && $request['price'] == 50
            && $request['category'] === 'Elettronica'
            && $request['photos'] === 2
            && ! isset($request['labels'], $request['safe_search']) // Google Vision rimosso dal progetto
            && $request['lang'] === 'es');
    }

    public function test_shows_the_suggestion_without_changing_the_article(): void
    {
        $this->fakeReview();
        $article = $this->pendingArticleWithImages();

        Livewire::actingAs($this->revisor)->test(ReviewAssistant::class, ['articleId' => $article->id])
            ->call('ask')
            ->assertSee(__('ui.review_ai_reject'))
            ->assertSee('Pagamento fuori dal sito')
            ->assertSee(__('ui.review_flag_contact_details'))
            ->assertSee(__('ui.review_ai_disclaimer'));

        $this->assertNull($article->fresh()->is_accepted);
    }

    public function test_suggestion_is_cached_per_article_version(): void
    {
        $this->fakeReview();
        $article = Article::factory()->create();

        Livewire::actingAs($this->revisor)->test(ReviewAssistant::class, ['articleId' => $article->id])->call('ask');
        // Riaprendo la dashboard il parere compare subito, senza nuove chiamate
        Livewire::actingAs($this->revisor)->test(ReviewAssistant::class, ['articleId' => $article->id])
            ->assertSee(__('ui.review_ai_reject'));
        Http::assertSentCount(1);

        // L'autore modifica l'annuncio: il parere va richiesto di nuovo
        $this->travel(1)->minutes();
        $article->update(['title' => 'Titolo cambiato']);
        Livewire::actingAs($this->revisor)->test(ReviewAssistant::class, ['articleId' => $article->id])
            ->assertSet('suggestion', null);
    }

    public function test_rules_only_suggestion_is_labelled(): void
    {
        $this->fakeReview(['recommendation' => 'unsure', 'model_used' => false, 'reasons' => [], 'flags' => []]);

        Livewire::actingAs($this->revisor)->test(ReviewAssistant::class, ['articleId' => Article::factory()->create()->id])
            ->call('ask')
            ->assertSee(__('ui.review_ai_unsure'))
            ->assertSee(__('ui.review_ai_rules_only'));
    }

    public function test_agent_down_is_reported(): void
    {
        Http::fake(['agent.test/review' => Http::response(['detail' => 'errore'], 500)]);

        Livewire::actingAs($this->revisor)->test(ReviewAssistant::class, ['articleId' => Article::factory()->create()->id])
            ->call('ask')
            ->assertSet('unavailable', true)
            ->assertSee(__('ui.assistant_unavailable'));
    }

    public function test_only_revisors_can_use_it(): void
    {
        $article = Article::factory()->create();

        Livewire::actingAs(User::factory()->create())->test(ReviewAssistant::class, ['articleId' => $article->id])
            ->assertForbidden();
        Livewire::test(ReviewAssistant::class, ['articleId' => $article->id])->assertForbidden();
    }

    public function test_article_id_cannot_be_tampered_from_the_browser(): void
    {
        // Proprietà #[Locked]: Livewire rifiuta la modifica che arriverebbe dal browser
        $this->expectException(CannotUpdateLockedPropertyException::class);

        Livewire::actingAs($this->revisor)->test(ReviewAssistant::class, ['articleId' => Article::factory()->create()->id])
            ->set('articleId', 999);
    }
}
