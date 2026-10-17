<?php

namespace Tests\Feature;

use App\Livewire\CreateArticleForm;
use App\Livewire\EditArticleForm;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class ArticleAiDraftTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Category $electronics;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.ai_agent.url' => 'http://agent.test', 'services.ai_agent.token' => 'segreto']);
        $this->user = User::factory()->create();
        $this->electronics = Category::create(['name' => 'Elettronica']);
        Category::create(['name' => 'Sport']);
        RateLimiter::clear('describe:'.$this->user->id);
    }

    private function fakeAgent(array $draft): void
    {
        Http::fake(['agent.test/describe' => Http::response([
            'found' => true,
            'title' => 'Lavatrice Bosch',
            'description' => 'Lavatrice Bosch. Colore: bianco. Caratteristiche visibili: oblò, display.',
            'category' => 'Elettronica',
            ...$draft,
        ])]);
    }

    private function withPhoto()
    {
        return Livewire::actingAs($this->user)->test(CreateArticleForm::class)
            ->set('temporary_images', [UploadedFile::fake()->image('lavatrice.png', 2000, 1500)]);
    }

    public function test_draft_fills_empty_fields_and_marks_the_category_to_check(): void
    {
        $this->fakeAgent([]);

        $this->withPhoto()
            ->assertSee(__('ui.ai_draft_button'))
            ->call('suggestFromPhoto')
            ->assertHasNoErrors()
            ->assertSet('form.title', 'Lavatrice Bosch')
            ->assertSet('form.description', 'Lavatrice Bosch. Colore: bianco. Caratteristiche visibili: oblò, display.')
            ->assertSet('form.category', $this->electronics->id)
            ->assertSet('form.price', null) // il prezzo lo decide sempre l'utente
            ->assertSet('categoryToCheck', true)
            ->assertSet('suggestion', 'filled')
            ->assertSee(__('ui.ai_draft_category_check'))
            // Scegliere la categoria a mano la conferma
            ->set('form.category', $this->electronics->id)
            ->assertSet('categoryToCheck', false);
    }

    public function test_photo_is_resized_and_sent_with_the_site_categories_and_language(): void
    {
        $this->fakeAgent([]);
        app()->setLocale('en');

        $this->withPhoto()->call('suggestFromPhoto');

        Http::assertSent(function (Request $request) {
            $image = base64_decode($request['image']);
            [$width, $height, $type] = getimagesizefromstring($image);

            return $request->url() === 'http://agent.test/describe'
                && $request->hasHeader('Authorization', 'Bearer segreto')
                && $request['categories'] === ['Elettronica', 'Sport']
                && $request['lang'] === 'en'
                && $type === IMAGETYPE_JPEG
                && max($width, $height) === 768;
        });
    }

    public function test_fields_already_written_by_the_user_are_never_overwritten(): void
    {
        $this->fakeAgent([]);
        $sport = Category::where('name', 'Sport')->value('id');

        $this->withPhoto()
            ->set('form.title', 'Il mio titolo')
            ->set('form.category', $sport)
            ->call('suggestFromPhoto')
            ->assertSet('form.title', 'Il mio titolo')
            ->assertSet('form.category', $sport)
            ->assertSet('form.description', 'Lavatrice Bosch. Colore: bianco. Caratteristiche visibili: oblò, display.')
            ->assertSet('categoryToCheck', false);

        $this->withPhoto()
            ->set('form.title', 'Titolo')->set('form.description', 'Descrizione già scritta')->set('form.category', $sport)
            ->call('suggestFromPhoto')
            ->assertSet('suggestion', 'kept')
            ->assertSee(__('ui.ai_draft_kept'));
    }

    public function test_unknown_categories_are_ignored(): void
    {
        $this->fakeAgent(['category' => 'Strumenti musicali']);

        $this->withPhoto()->call('suggestFromPhoto')
            ->assertSet('form.category', null)
            ->assertSet('categoryToCheck', false)
            ->assertSet('form.title', 'Lavatrice Bosch');
    }

    public function test_friendly_messages_when_nothing_is_recognised_or_the_agent_is_down(): void
    {
        Http::fake(['agent.test/describe' => Http::response(['found' => false])]);
        $this->withPhoto()->call('suggestFromPhoto')->assertHasErrors('ai')->assertSee(__('ui.ai_draft_not_found'))->assertSet('form.title', null);

        Http::fake(['agent.test/describe' => fn () => throw new ConnectionException('rifiutata')]);
        $this->withPhoto()->call('suggestFromPhoto')->assertSee(__('ui.ai_draft_unavailable'));

        Http::fake(['agent.test/describe' => Http::response(['detail' => 'Ollama spento'], 503)]);
        $this->withPhoto()->call('suggestFromPhoto')->assertSee(__('ui.ai_draft_unavailable'));

        Http::fake(['agent.test/describe' => Http::response(['qualcosa' => 'di strano'])]);
        $this->withPhoto()->call('suggestFromPhoto')->assertSee(__('ui.ai_draft_unavailable'));
    }

    public function test_rate_limit(): void
    {
        $this->fakeAgent([]);
        $component = $this->withPhoto();

        for ($i = 0; $i < CreateArticleForm::MAX_SUGGESTIONS_PER_MINUTE; $i++) {
            $component->call('suggestFromPhoto')->assertHasNoErrors();
        }
        $component->call('suggestFromPhoto')->assertHasErrors('ai');

        Http::assertSentCount(CreateArticleForm::MAX_SUGGESTIONS_PER_MINUTE);
    }

    public function test_button_needs_a_photo_and_a_configured_agent_and_is_not_in_the_edit_form(): void
    {
        Http::fake();

        // Senza foto: niente pulsante e nessuna chiamata
        Livewire::actingAs($this->user)->test(CreateArticleForm::class)
            ->assertDontSee(__('ui.ai_draft_button'))
            ->call('suggestFromPhoto')
            ->assertHasNoErrors();

        config(['services.ai_agent.token' => '']);
        $this->withPhoto()->assertDontSee(__('ui.ai_draft_button'))->call('suggestFromPhoto');

        $article = Article::factory()->for($this->user)->create();
        Livewire::actingAs($this->user)->test(EditArticleForm::class, ['article' => $article])
            ->set('temporary_images', [UploadedFile::fake()->image('foto.png')])
            ->assertDontSee(__('ui.ai_draft_button'));

        Http::assertNothingSent();
    }
}
