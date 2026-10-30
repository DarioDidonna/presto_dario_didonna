<?php

namespace Tests\Feature;

use App\Mail\BecomeRevisor;
use App\Models\Article;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_and_normal_user_cannot_accept_or_reject(): void
    {
        $article = Article::factory()->create();

        $this->patch(route('accept', $article))->assertRedirect(route('homepage'));
        $this->patch(route('reject', $article))->assertRedirect(route('homepage'));
        $this->actingAs(User::factory()->create())->patch(route('accept', $article))->assertRedirect(route('homepage'));

        $this->assertNull($article->fresh()->is_accepted);
    }

    public function test_normal_user_cannot_open_revisor_dashboard(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('revisor.index'))
            ->assertRedirect(route('homepage'))
            ->assertSessionHas('errorMessage');
    }

    public function test_make_revisor_requires_valid_signature(): void
    {
        $user = User::factory()->create();

        $this->get(route('make.revisor', $user))->assertForbidden();
        $this->post(route('make.revisor.store', $user))->assertForbidden();
        $this->assertFalse((bool) $user->fresh()->is_revisor);

        $expired = URL::temporarySignedRoute('make.revisor', now()->subMinute(), ['user' => $user], absolute: false);
        $this->get($expired)->assertForbidden();
        $this->post($expired)->assertForbidden();
        $this->assertFalse((bool) $user->fresh()->is_revisor);
    }

    public function test_opening_the_link_does_not_promote_and_confirming_works_once(): void
    {
        $user = User::factory()->create();
        $url = URL::temporarySignedRoute('make.revisor', now()->addDay(), ['user' => $user], absolute: false);

        // Un antivirus di posta che apre il link non deve promuovere nessuno
        $this->get($url)->assertOk()->assertSee($user->name)->assertSee('action="'.e($url).'"', false);
        $this->assertFalse((bool) $user->fresh()->is_revisor);

        $this->post($url)->assertRedirect(route('homepage'));
        $this->assertTrue((bool) $user->fresh()->is_revisor);

        // Monouso: se il revisore viene poi revocato, lo stesso link non lo riattiva
        $user->forceFill(['is_revisor' => false])->save();
        $this->post($url)->assertForbidden();
        $this->assertFalse((bool) $user->fresh()->is_revisor);
    }

    public function test_signed_link_cannot_be_reused_for_another_user(): void
    {
        $victim = User::factory()->create();
        $attacker = User::factory()->create();
        $url = URL::temporarySignedRoute('make.revisor', now()->addDay(), ['user' => $victim], absolute: false);

        $this->post(str_replace("/{$victim->id}?", "/{$attacker->id}?", $url))->assertForbidden();
        $this->assertFalse((bool) $attacker->fresh()->is_revisor);
    }

    public function test_revisor_link_ignores_a_forged_host_header(): void
    {
        Mail::fake();
        config(['app.url' => 'https://presto.test']);
        $user = User::factory()->create();

        $this->actingAs($user)->post('http://evil.example/revisor/request');

        Mail::assertSent(BecomeRevisor::class, fn (BecomeRevisor $mail) => str_starts_with($mail->approvalUrl, 'https://presto.test/make/revisor/'));
    }

    public function test_become_revisor_requires_post_auth_and_is_throttled(): void
    {
        Mail::fake();
        $user = User::factory()->create();

        $this->get('/revisor/request')->assertStatus(405);
        $this->post(route('become.revisor'))->assertRedirect(route('login'));

        foreach (range(1, 3) as $n) {
            $this->actingAs($user)->post(route('become.revisor'))->assertRedirect(route('homepage'));
        }
        $this->actingAs($user)->post(route('become.revisor'))->assertStatus(429);

        Mail::assertSent(BecomeRevisor::class, 3);
    }

    public function test_unapproved_article_is_visible_only_to_author_and_revisors(): void
    {
        foreach ([Article::factory()->create(), Article::factory()->rejected()->create()] as $article) {
            $this->app['auth']->forgetGuards();
            $this->get(route('article.show', $article))->assertNotFound();
            $this->actingAs(User::factory()->create())->get(route('article.show', $article))->assertNotFound();
            $this->actingAs($article->user)->get(route('article.show', $article))->assertOk();
            $this->actingAs(User::factory()->revisor()->create())->get(route('article.show', $article))->assertOk();
        }
    }

    public function test_invalid_locale_is_rejected(): void
    {
        $this->post('/lingua/xx')->assertNotFound();
        $this->post('/lingua/..%2F..%2Fetc')->assertNotFound();

        $this->post('/lingua/es')->assertRedirect();
        $this->assertSame('es', session('locale'));
    }

    public function test_tampered_session_locale_falls_back_to_italian(): void
    {
        $this->withSession(['locale' => 'xx'])->get('/')->assertOk()->assertSee('lang="it"', false);
    }

    public function test_reset_link_ignores_a_forged_host_header(): void
    {
        Notification::fake();
        config(['app.url' => 'https://presto.test']);
        $user = User::factory()->create();

        $this->post('http://evil.example/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
            return str_starts_with($notification->toMail($user)->actionUrl, 'https://presto.test/reset-password/');
        });
    }

    public function test_forged_host_is_rejected_in_production(): void
    {
        config(['app.url' => 'https://presto.test']);
        $this->app['env'] = 'production';

        try {
            $this->get('https://presto.test/up')->assertOk();
            $this->get('http://evil.example/up')->assertStatus(400);
        } finally {
            // Regola statica di Symfony: non deve restare attiva per i test successivi
            Request::setTrustedHosts([]);
        }
    }
}
