<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        \Illuminate\Support\Facades\Notification::fake();
        $user = User::factory()->create();

        $this->from('/forgot-password')->post('/forgot-password', ['email' => $user->email])
            ->assertRedirect('/forgot-password')
            ->assertSessionHas('status', 'Ti abbiamo inviato via email il link per reimpostare la password.');

        \Illuminate\Support\Facades\Notification::assertSentTo($user, \Illuminate\Auth\Notifications\ResetPassword::class,
            function ($notification) use ($user) {
                $mail = $notification->toMail($user);
                return $mail->subject === 'Reimposta la tua password' && $mail->actionText === 'Reimposta password';
            });
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create();
        $token = \Illuminate\Support\Facades\Password::createToken($user);

        $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NuovissimaPassword1!',
            'password_confirmation' => 'NuovissimaPassword1!',
        ])->assertRedirect(route('login'))->assertSessionHas('status', 'La tua password è stata reimpostata.');

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('NuovissimaPassword1!', $user->fresh()->password));
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

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('password', $user->fresh()->password));
    }

    public function test_unused_two_factor_routes_are_disabled(): void
    {
        $this->get('/two-factor-challenge')->assertNotFound();
    }
}
