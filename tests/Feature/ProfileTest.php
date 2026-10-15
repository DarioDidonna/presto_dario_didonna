<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('profile.show'))->assertRedirect(route('login'));
        $this->post(route('profile.avatar.update'))->assertRedirect(route('login'));
        $this->delete(route('profile.avatar.destroy'))->assertRedirect(route('login'));
    }

    public function test_profile_shows_user_data_and_only_own_articles_with_status(): void
    {
        $user = User::factory()->create(['name' => 'Giulia Bianchi', 'email' => 'giulia@example.com']);
        Article::factory()->accepted()->for($user)->create(['title' => 'Bici pubblicata']);
        Article::factory()->for($user)->create(['title' => 'Lampada in attesa']);
        Article::factory()->rejected()->for($user)->create(['title' => 'Quadro rifiutato']);
        Article::factory()->accepted()->create(['title' => 'Annuncio di un altro']);

        $response = $this->actingAs($user)->get(route('profile.show'))->assertOk();

        $response->assertSee('Giulia Bianchi')
            ->assertSee('giulia@example.com')
            ->assertSee(__('ui.role_user'))
            ->assertSee('Bici pubblicata')
            ->assertSee('Lampada in attesa')
            ->assertSee('Quadro rifiutato')
            ->assertDontSee('Annuncio di un altro')
            ->assertViewHas('counts', ['published' => 1, 'pending' => 1, 'rejected' => 1]);
    }

    public function test_user_can_upload_avatar_which_is_cropped_and_shown_in_navbar(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('profile.avatar.update'), ['avatar' => UploadedFile::fake()->image('foto.png', 600, 400)])
            ->assertRedirect(route('profile.show'))
            ->assertSessionHas('message', __('ui.avatar_updated'));

        $path = $user->fresh()->avatar;
        $this->assertNotNull($path);
        $this->assertStringStartsWith("avatars/{$user->id}/", $path);
        Storage::disk('public')->assertExists($path);

        [$width, $height, $type] = getimagesize(Storage::disk('public')->path($path));
        $this->assertSame([256, 256, IMAGETYPE_JPEG], [$width, $height, $type]);

        // La navbar mostra la foto al posto delle iniziali
        $this->actingAs($user->fresh())->get('/')->assertSee(Storage::disk('public')->url($path), false);
    }

    public function test_new_avatar_replaces_and_deletes_the_old_file(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('profile.avatar.update'), ['avatar' => UploadedFile::fake()->image('a.jpg', 300, 300)]);
        $old = $user->fresh()->avatar;

        $this->actingAs($user)->post(route('profile.avatar.update'), ['avatar' => UploadedFile::fake()->image('b.jpg', 300, 300)]);
        $new = $user->fresh()->avatar;

        $this->assertNotSame($old, $new);
        Storage::disk('public')->assertMissing($old);
        Storage::disk('public')->assertExists($new);
    }

    public function test_user_can_remove_avatar(): void
    {
        $user = User::factory()->create(['name' => 'Luca Verdi']);
        $this->actingAs($user)->post(route('profile.avatar.update'), ['avatar' => UploadedFile::fake()->image('a.jpg', 300, 300)]);
        $path = $user->fresh()->avatar;

        $this->actingAs($user)->delete(route('profile.avatar.destroy'))->assertRedirect(route('profile.show'));

        $this->assertNull($user->fresh()->avatar);
        Storage::disk('public')->assertMissing($path);
        $this->actingAs($user->fresh())->get('/')->assertSee('LV');
    }

    public function test_invalid_avatar_uploads_are_rejected(): void
    {
        $user = User::factory()->create();

        foreach ([
            UploadedFile::fake()->create('documento.pdf', 100, 'application/pdf'),
            UploadedFile::fake()->image('enorme.jpg', 300, 300)->size(3000),
            UploadedFile::fake()->image('minuscola.jpg', 20, 20),
            UploadedFile::fake()->create('icona.gif', 10, 'image/gif'),
        ] as $file) {
            $this->actingAs($user)
                ->from(route('profile.show'))
                ->post(route('profile.avatar.update'), ['avatar' => $file])
                ->assertRedirect(route('profile.show'))
                ->assertSessionHasErrors('avatar');
        }

        $this->assertNull($user->fresh()->avatar);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_avatar_cannot_be_set_through_registration(): void
    {
        $this->post('/register', [
            'name' => 'Furbo',
            'email' => 'furbo@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'avatar' => '../../.env',
        ]);

        $this->assertNull(User::where('email', 'furbo@example.com')->first()->avatar);
    }

    public function test_navbar_links_to_profile(): void
    {
        $this->actingAs(User::factory()->create())->get('/')->assertSee(route('profile.show'), false);
    }

    public function test_quick_actions_depend_on_role(): void
    {
        $this->actingAs(User::factory()->create())->get(route('profile.show'))
            ->assertSee(__('ui.quick_actions'))
            ->assertSee(__('ui.apply_revisor'))
            ->assertSee('action="' . route('become.revisor') . '"', false);

        $this->actingAs(User::factory()->revisor()->create())->get(route('profile.show'))
            ->assertDontSee(__('ui.apply_revisor'))
            ->assertSee('href="' . route('revisor.index') . '"', false);
    }

    public function test_user_can_change_password_from_profile(): void
    {
        $user = User::factory()->create(); // password: "password"

        $this->actingAs($user)
            ->from(route('profile.show'))
            ->put(route('user-password.update'), [
                'current_password' => 'password',
                'password' => 'NuovaPassword123!',
                'password_confirmation' => 'NuovaPassword123!',
            ])
            ->assertRedirect(route('profile.show'))
            ->assertSessionHasNoErrors();

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('NuovaPassword123!', $user->fresh()->password));

        $this->actingAs($user->fresh())->withSession(['status' => 'password-updated'])
            ->get(route('profile.show'))
            ->assertSee(__('ui.password_updated'));
    }

    public function test_wrong_current_password_shows_italian_error(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('profile.show'))
            ->put(route('user-password.update'), [
                'current_password' => 'sbagliata',
                'password' => 'NuovaPassword123!',
                'password_confirmation' => 'Diversa123!',
            ])
            ->assertRedirect(route('profile.show'))
            ->assertSessionHasErrorsIn('updatePassword', [
                'current_password' => 'La password attuale non è corretta.',
                'password' => 'La conferma del campo password non corrisponde.',
            ]);

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('password', $user->fresh()->password));
    }

    public function test_login_failure_message_is_translated(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'sbagliata'])
            ->assertSessionHasErrors(['email' => 'Le credenziali inserite non corrispondono ai nostri archivi.']);
    }

    public function test_user_can_change_name_and_email_stays_the_same(): void
    {
        $user = User::factory()->create(['name' => 'Mario Rossi', 'email' => 'mario@example.com']);

        $this->actingAs($user)
            ->from(route('profile.show'))
            ->put(route('user-profile-information.update'), ['name' => '  Mario Bianchi  '])
            ->assertRedirect(route('profile.show'))
            ->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertSame('Mario Bianchi', $user->name);
        $this->assertSame('mario@example.com', $user->email);

        $this->actingAs($user)->withSession(['status' => 'profile-information-updated'])
            ->get(route('profile.show'))
            ->assertSee(__('ui.name_updated'))
            ->assertSee('MB');
    }

    public function test_invalid_name_is_rejected_with_italian_message(): void
    {
        $user = User::factory()->create(['name' => 'Mario Rossi']);

        foreach (['' => 'Il campo nome utente è obbligatorio.', 'A' => 'Il campo nome utente deve contenere almeno 2 caratteri.'] as $name => $message) {
            $this->actingAs($user)
                ->from(route('profile.show'))
                ->put(route('user-profile-information.update'), ['name' => $name])
                ->assertRedirect(route('profile.show'))
                ->assertSessionHasErrorsIn('updateProfileInformation', ['name' => $message]);
        }

        $this->assertSame('Mario Rossi', $user->fresh()->name);
    }

    public function test_name_form_reopens_when_validation_fails(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->from(route('profile.show'))->put(route('user-profile-information.update'), ['name' => 'A']);

        $this->actingAs($user)->get(route('profile.show'))
            ->assertSee('id="editNameForm" class="collapse mt-3 show"', false);
    }

    public function test_guest_cannot_change_name(): void
    {
        $this->put(route('user-profile-information.update'), ['name' => 'Hacker'])->assertRedirect(route('login'));
    }
}
