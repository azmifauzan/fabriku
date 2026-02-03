<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Set up telegram config for tests
    config(['telegram.bot_token' => 'test-bot-token']);
    config(['telegram.webhook_secret' => 'test-secret']);
    config(['telegram.admin_chat_id' => '1867506155']);
});

it('rejects webhook without secret token when secret is configured', function () {
    $response = $this->postJson('/api/telegram/webhook', [
        'message' => [
            'chat' => ['id' => '12345'],
            'text' => 'Hello',
            'from' => ['id' => '12345'],
        ],
    ]);

    $response->assertStatus(401);
});

it('accepts webhook with correct secret token', function () {
    Http::fake();

    $response = $this->postJson('/api/telegram/webhook', [
        'message' => [
            'chat' => ['id' => '12345'],
            'text' => '/start',
            'from' => ['id' => '12345'],
        ],
    ], [
        'X-Telegram-Bot-Api-Secret-Token' => 'test-secret',
    ]);

    $response->assertOk();
    $response->assertJson(['ok' => true]);
});

it('handles /start command for new users', function () {
    Http::fake();

    $response = $this->postJson('/api/telegram/webhook', [
        'message' => [
            'chat' => ['id' => '12345'],
            'text' => '/start',
            'from' => ['id' => '12345'],
        ],
    ], [
        'X-Telegram-Bot-Api-Secret-Token' => 'test-secret',
    ]);

    $response->assertOk();

    Http::assertSent(function ($request) {
        return str_contains($request->url(), '/sendMessage') &&
               $request['chat_id'] === '12345' &&
               str_contains($request['text'], 'Selamat datang');
    });
});

it('handles /start command for connected users', function () {
    Http::fake();

    $tenant = Tenant::factory()->create();
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'telegram_chat_id' => '12345',
    ]);

    $response = $this->postJson('/api/telegram/webhook', [
        'message' => [
            'chat' => ['id' => '12345'],
            'text' => '/start',
            'from' => ['id' => '12345'],
        ],
    ], [
        'X-Telegram-Bot-Api-Secret-Token' => 'test-secret',
    ]);

    $response->assertOk();

    Http::assertSent(function ($request) use ($user) {
        return str_contains($request->url(), '/sendMessage') &&
               $request['chat_id'] === '12345' &&
               str_contains($request['text'], $user->name);
    });
});

it('connects user with valid token', function () {
    Http::fake();

    $tenant = Tenant::factory()->create();
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
    ]);
    $token = $user->generateTelegramConnectToken();

    $response = $this->postJson('/api/telegram/webhook', [
        'message' => [
            'chat' => ['id' => '12345'],
            'text' => "CONNECT {$token}",
            'from' => ['id' => '12345'],
        ],
    ], [
        'X-Telegram-Bot-Api-Secret-Token' => 'test-secret',
    ]);

    $response->assertOk();

    $user->refresh();
    expect($user->telegram_chat_id)->toBe('12345');
    expect($user->telegram_connect_token)->toBeNull();
});

it('rejects invalid connect token', function () {
    Http::fake();

    $response = $this->postJson('/api/telegram/webhook', [
        'message' => [
            'chat' => ['id' => '12345'],
            'text' => 'CONNECT AAAABBBB',
            'from' => ['id' => '12345'],
        ],
    ], [
        'X-Telegram-Bot-Api-Secret-Token' => 'test-secret',
    ]);

    $response->assertOk();

    Http::assertSent(function ($request) {
        return str_contains($request->url(), '/sendMessage') &&
               str_contains($request['text'], 'tidak valid');
    });
});

it('handles messages from connected users', function () {
    Http::fake();

    $tenant = Tenant::factory()->create();
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'telegram_chat_id' => '12345',
    ]);

    $response = $this->postJson('/api/telegram/webhook', [
        'message' => [
            'chat' => ['id' => '12345'],
            'text' => 'Halo, berapa penjualan hari ini?',
            'from' => ['id' => '12345'],
        ],
    ], [
        'X-Telegram-Bot-Api-Secret-Token' => 'test-secret',
    ]);

    $response->assertOk();

    // Should send a response back
    Http::assertSent(function ($request) {
        return str_contains($request->url(), '/sendMessage') &&
               $request['chat_id'] === '12345';
    });
});

it('handles /status command', function () {
    Http::fake();

    $tenant = Tenant::factory()->create([
        'subscription_plan' => 'trial',
        'subscription_expires_at' => now()->addDays(30),
    ]);
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'telegram_chat_id' => '12345',
    ]);

    $response = $this->postJson('/api/telegram/webhook', [
        'message' => [
            'chat' => ['id' => '12345'],
            'text' => '/status',
            'from' => ['id' => '12345'],
        ],
    ], [
        'X-Telegram-Bot-Api-Secret-Token' => 'test-secret',
    ]);

    $response->assertOk();

    Http::assertSent(function ($request) use ($user) {
        return str_contains($request->url(), '/sendMessage') &&
               str_contains($request['text'], $user->name) &&
               str_contains($request['text'], 'Status Akun');
    });
});

it('handles /disconnect command', function () {
    Http::fake();

    $tenant = Tenant::factory()->create();
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'telegram_chat_id' => '12345',
    ]);

    $response = $this->postJson('/api/telegram/webhook', [
        'message' => [
            'chat' => ['id' => '12345'],
            'text' => '/disconnect',
            'from' => ['id' => '12345'],
        ],
    ], [
        'X-Telegram-Bot-Api-Secret-Token' => 'test-secret',
    ]);

    $response->assertOk();

    $user->refresh();
    expect($user->telegram_chat_id)->toBeNull();

    Http::assertSent(function ($request) {
        return str_contains($request->url(), '/sendMessage') &&
               str_contains($request['text'], 'berhasil diputus');
    });
});
