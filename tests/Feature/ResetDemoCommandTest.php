<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResetDemoCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_refuses_to_run_in_production(): void
    {
        $user = User::factory()->create();
        $this->app['env'] = 'production';

        $this->artisan('app:demo-reset', ['--force' => true])
            ->expectsOutput('Comando non disponibile in produzione.')
            ->assertFailed();

        $this->assertModelExists($user);
    }

    public function test_does_nothing_without_confirmation(): void
    {
        $user = User::factory()->create();

        $this->artisan('app:demo-reset')
            ->expectsConfirmation('Cancella TUTTO il database locale (anche il tuo account) e le foto caricate. Continuare?', 'no')
            ->expectsOutput('Operazione annullata.')
            ->assertSuccessful();

        $this->assertModelExists($user);
    }

    public function test_composer_shortcut_exists(): void
    {
        $scripts = json_decode(file_get_contents(base_path('composer.json')), true)['scripts'];

        $this->assertSame(['@php artisan app:demo-reset'], $scripts['demo']);
    }
}
