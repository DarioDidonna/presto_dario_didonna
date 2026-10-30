<?php

namespace Tests\Feature;

use App\Livewire\CreateArticleForm;
use App\Models\Article;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_is_not_revisor(): void
    {
        $this->post('/register', [
            'name' => 'Mario Rossi',
            'email' => 'mario@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertRedirect('/create/article');

        $this->assertAuthenticated();
        $user = User::where('email', 'mario@example.com')->first();
        $this->assertFalse((bool) $user->is_revisor);
    }

    public function test_registration_cannot_set_is_revisor(): void
    {
        $this->post('/register', [
            'name' => 'Furbo',
            'email' => 'furbo@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'is_revisor' => 1,
        ]);

        $this->assertFalse((bool) User::where('email', 'furbo@example.com')->first()->is_revisor);
    }

    public function test_user_can_login_and_logout(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/create/article');
        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_wrong_password_is_rejected_and_throttled(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 5) as $attempt) {
            $this->post('/login', ['email' => $user->email, 'password' => 'sbagliata'])->assertSessionHasErrors('email');
        }
        $this->post('/login', ['email' => $user->email, 'password' => 'sbagliata'])->assertStatus(429);
        $this->assertGuest();
    }

    public function test_password_reset_pages_render_instead_of_500(): void
    {
        $this->get('/forgot-password')->assertOk()->assertSee(__('ui.forgot_password_title'));
        $this->get('/reset-password/token-di-prova?email=mario@example.com')
            ->assertOk()
            ->assertSee('value="token-di-prova"', false)
            ->assertSee('value="mario@example.com"', false);
        $this->actingAs(User::factory()->create())->get('/user/confirm-password')->assertOk();
    }

    public function test_reset_link_email_is_sent_and_translated(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->from('/forgot-password')->post('/forgot-password', ['email' => $user->email])
            ->assertRedirect('/forgot-password')
            ->assertSessionHas('status', 'Ti abbiamo inviato via email il link per reimpostare la password.');

        Notification::assertSentTo($user, ResetPassword::class,
            function ($notification) use ($user) {
                $mail = $notification->toMail($user);

                return $mail->subject === 'Reimposta la tua password' && $mail->actionText === 'Reimposta password';
            });
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NuovissimaPassword1!',
            'password_confirmation' => 'NuovissimaPassword1!',
        ])->assertRedirect(route('login'))->assertSessionHas('status', 'La tua password è stata reimpostata.');

        $this->assertTrue(Hash::check('NuovissimaPassword1!', $user->fresh()->password));
    }

    public function test_invalid_reset_token_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->from('/reset-password/falso')->post('/reset-password', [
            'token' => 'falso',
            'email' => $user->email,
            'password' => 'NuovissimaPassword1!',
            'password_confirmation' => 'NuovissimaPassword1!',
        ])->assertSessionHasErrors(['email' => 'Il link per reimpostare la password non è valido o è scaduto.']);

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_unused_two_factor_routes_are_disabled(): void
    {
        $this->get('/two-factor-challenge')->assertNotFound();
    }

    public function test_new_users_must_verify_their_email_before_selling_or_buying(): void
    {
        Notification::fake();

        $this->post('/register', [
            'name' => 'Mario Rossi',
            'email' => 'mario@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $user = User::where('email', 'mario@example.com')->first();
        Notification::assertSentTo($user, VerifyEmail::class);

        $this->get(route('create.article'))->assertRedirect(route('verification.notice'));
        $this->get(route('verification.notice'))->assertOk()->assertSee(__('ui.verify_email_title'));
        $this->post(route('become.revisor'))->assertRedirect(route('verification.notice'));

        $article = Article::factory()->accepted()->create();
        $this->post(route('cart.add', $article))->assertRedirect(route('verification.notice'));

        Livewire::actingAs($user)->test(CreateArticleForm::class)->call('store')->assertForbidden();

        $link = URL::temporarySignedRoute('verification.verify', now()->addHour(), ['id' => $user->id, 'hash' => sha1($user->email)]);
        $this->get($link);

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->get(route('create.article'))->assertOk();
    }

    public function test_changing_email_needs_the_password_and_a_new_verification(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'prima@example.com', 'password' => 'Password123!']);

        $this->actingAs($user)->put(route('user-profile-information.update'), ['name' => $user->name, 'email' => 'dopo@example.com'])
            ->assertSessionHasErrorsIn('updateProfileInformation', 'current_password');
        $this->actingAs($user)->put(route('user-profile-information.update'), ['name' => $user->name, 'email' => 'dopo@example.com', 'current_password' => 'sbagliata'])
            ->assertSessionHasErrorsIn('updateProfileInformation', 'current_password');
        $this->assertSame('prima@example.com', $user->fresh()->email);

        $this->actingAs($user)->put(route('user-profile-information.update'), ['name' => $user->name, 'email' => 'dopo@example.com', 'current_password' => 'Password123!'])
            ->assertSessionHasNoErrors();

        $this->assertSame('dopo@example.com', $user->fresh()->email);
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
        Notification::assertSentTo($user, VerifyEmail::class);
    }
}
