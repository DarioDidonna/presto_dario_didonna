<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiSetupCommandTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake(['localhost:11434/*' => Http::response(['models' => [['name' => 'qwen2.5:3b']]])]);
        $this->dir = sys_get_temp_dir().'/ai-setup-'.uniqid();
        mkdir($this->dir);
        file_put_contents("{$this->dir}/laravel.env", "APP_NAME=Presto\nAI_AGENT_URL=http://127.0.0.1:8001\nAI_AGENT_TOKEN=\n");
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob("{$this->dir}/*"));
        rmdir($this->dir);
        parent::tearDown();
    }

    private function runSetup(): void
    {
        $this->artisan('app:ai-setup', ['--env' => "{$this->dir}/laravel.env", '--agent-env' => "{$this->dir}/agent.env"])
            ->assertSuccessful();
    }

    private function value(string $file, string $key): ?string
    {
        preg_match('/^'.$key.'=(.*)$/m', file_get_contents("{$this->dir}/{$file}"), $m);

        return $m[1] ?? null;
    }

    public function test_generates_the_same_token_in_both_env_files(): void
    {
        $this->runSetup();

        $token = $this->value('laravel.env', 'AI_AGENT_TOKEN');
        $this->assertSame(64, strlen($token));
        $this->assertSame($token, $this->value('agent.env', 'PRESTO_AI_API_TOKEN'));
        $this->assertSame('qwen2.5:3b', $this->value('agent.env', 'PRESTO_AI_MODEL')); // copiato da .env.example
        $this->assertSame(1, substr_count(file_get_contents("{$this->dir}/laravel.env"), 'AI_AGENT_TOKEN='));
    }

    public function test_running_it_again_keeps_the_existing_token(): void
    {
        $this->runSetup();
        $first = $this->value('laravel.env', 'AI_AGENT_TOKEN');

        $this->runSetup();

        $this->assertSame($first, $this->value('laravel.env', 'AI_AGENT_TOKEN'));
        $this->assertSame($first, $this->value('agent.env', 'PRESTO_AI_API_TOKEN'));
    }

    public function test_refuses_to_run_in_production(): void
    {
        $this->app['env'] = 'production';

        $this->artisan('app:ai-setup', ['--env' => "{$this->dir}/laravel.env", '--agent-env' => "{$this->dir}/agent.env"])
            ->assertFailed();
        $this->assertNull($this->value('laravel.env', 'AI_AGENT_TOKEN') ?: null);
    }

    public function test_reports_the_optional_vision_model(): void
    {
        $this->artisan('app:ai-setup', ['--env' => "{$this->dir}/laravel.env", '--agent-env' => "{$this->dir}/agent.env"])
            ->expectsOutputToContain('qwen2.5vl:3b')
            ->assertSuccessful();

        $this->assertSame('qwen2.5vl:3b', $this->value('agent.env', 'PRESTO_AI_VISION_MODEL')); // da ai-agent/.env.example
    }
}
