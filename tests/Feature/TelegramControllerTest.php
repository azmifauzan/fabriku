<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['telegram.bot_token' => 'test-bot-token']);
    config(['telegram.bot_username' => 'FabrikuBot']);
});

it('requires authentication to access telegram settings page', function () {
    $response = $this->get('/settings/telegram');

    $response->assertRedirect('/login');
});

it('shows telegram settings page for authenticated user', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user)->get('/settings/telegram');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Settings/Telegram')
        ->has('isConnected')
        ->has('telegramChatId')
        ->has('connectToken')
        ->has('tokenExpiresAt')
    );
});

it('returns connected status when user has telegram linked', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'telegram_chat_id' => '12345678',
    ]);

    $response = $this->actingAs($user)->get('/settings/telegram');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Settings/Telegram')
        ->where('isConnected', true)
        ->where('telegramChatId', '12345678')
    );
});

it('generates connect token for user without telegram', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user)->post('/telegram/generate-token');

    $response->assertRedirect();

    $user->refresh();
    expect($user->telegram_connect_token)->not->toBeNull();
    expect($user->telegram_connect_token)->toHaveLength(8);
    expect($user->telegram_connect_token_expires_at)->not->toBeNull();
});

it('rejects token generation for users with telegram already connected', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'telegram_chat_id' => '12345678',
    ]);

    $response = $this->actingAs($user)->post('/telegram/generate-token');

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

it('disconnects telegram from user account', function () {
    Http::fake();

    $tenant = Tenant::factory()->create();
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'telegram_chat_id' => '12345678',
    ]);

    $response = $this->actingAs($user)->post('/telegram/disconnect');

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $user->refresh();
    expect($user->telegram_chat_id)->toBeNull();
});

it('rejects disconnect when telegram is not connected', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user)->post('/telegram/disconnect');

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

it('sends test message to connected telegram', function () {
    Http::fake();

    $tenant = Tenant::factory()->create();
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'telegram_chat_id' => '12345678',
    ]);

    $response = $this->actingAs($user)->post('/telegram/test');

    $response->assertRedirect();
    $response->assertSessionHas('success');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), '/sendMessage') &&
               $request['chat_id'] === '12345678' &&
               str_contains($request['text'], 'Pesan Tes');
    });
});
