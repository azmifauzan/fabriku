<?php

use App\Models\AssistantConversation;
use App\Models\AssistantMessage;
use App\Models\AssistantUsage;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Assistant\OpenAIService;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);

    // Mock OpenAI service as partial to allow other methods to work
    $this->mock(OpenAIService::class, function ($mock) {
        $mock->makePartial();
        $mock->shouldReceive('isConfigured')->andReturn(true);
        $mock->shouldReceive('getSystemPrompt')->andReturn('System prompt');
        $mock->shouldReceive('chat')->andReturn([
            'success' => true,
            'content' => 'Test response from assistant.',
            'tokens_input' => 100,
            'tokens_output' => 50,
        ]);
    });
});

test('guests cannot access assistant endpoints', function () {
    $this->postJson('/assistant/message', ['message' => 'Hello'])
        ->assertUnauthorized();

    $this->getJson('/assistant/history')
        ->assertUnauthorized();

    $this->postJson('/assistant/clear')
        ->assertUnauthorized();

    $this->getJson('/assistant/usage')
        ->assertUnauthorized();
});

test('authenticated users can get usage stats', function () {
    $response = $this->actingAs($this->user)
        ->getJson('/assistant/usage');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'usage' => [
                'today' => ['message_count'],
                'limit',
                'remaining',
                'plan',
            ],
        ]);
});

test('authenticated users can get empty conversation history', function () {
    $response = $this->actingAs($this->user)
        ->getJson('/assistant/history');

    $response->assertSuccessful()
        ->assertJson([
            'messages' => [],
        ]);
});

test('authenticated users can get conversation history', function () {
    $conversation = AssistantConversation::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->user->id,
        'channel' => 'web',
        'is_active' => true,
    ]);

    AssistantMessage::create([
        'conversation_id' => $conversation->id,
        'role' => 'user',
        'content' => 'Test message',
    ]);

    AssistantMessage::create([
        'conversation_id' => $conversation->id,
        'role' => 'assistant',
        'content' => 'Test response',
    ]);

    $response = $this->actingAs($this->user)
        ->getJson('/assistant/history');

    $response->assertSuccessful()
        ->assertJsonCount(2, 'messages');
});

test('authenticated users can clear conversation history', function () {
    $conversation = AssistantConversation::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->user->id,
        'channel' => 'web',
        'is_active' => true,
    ]);

    AssistantMessage::create([
        'conversation_id' => $conversation->id,
        'role' => 'user',
        'content' => 'Test message',
    ]);

    $response = $this->actingAs($this->user)
        ->postJson('/assistant/clear');

    $response->assertSuccessful()
        ->assertJson(['success' => true]);
});

test('send message requires message content', function () {
    $response = $this->actingAs($this->user)
        ->postJson('/assistant/message', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['message']);
});

test('send message validates message length', function () {
    $response = $this->actingAs($this->user)
        ->postJson('/assistant/message', [
            'message' => str_repeat('a', 5001),
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['message']);
});

test('send message works with mocked OpenAI', function () {
    $response = $this->actingAs($this->user)
        ->postJson('/assistant/message', [
            'message' => 'Halo',
        ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
        ])
        ->assertJsonStructure([
            'success',
            'message',
            'message_id',
        ]);

    // Check messages were created
    expect(AssistantMessage::count())->toBe(2);
    expect(AssistantMessage::where('role', 'user')->first()->content)->toBe('Halo');
    expect(AssistantMessage::where('role', 'assistant')->first()->content)->toBe('Test response from assistant.');
});

test('usage increments after sending message', function () {
    $this->actingAs($this->user)
        ->postJson('/assistant/message', [
            'message' => 'Hello',
        ]);

    $usage = AssistantUsage::where('user_id', $this->user->id)->first();

    expect($usage)->not->toBeNull();
    expect($usage->message_count)->toBe(1);
    expect($usage->tokens_input + $usage->tokens_output)->toBe(150);
});

test('conversation context is maintained', function () {
    // Send first message
    $this->actingAs($this->user)
        ->postJson('/assistant/message', ['message' => 'First message']);

    // Send second message
    $this->actingAs($this->user)
        ->postJson('/assistant/message', ['message' => 'Second message']);

    // Should have same conversation
    $conversations = AssistantConversation::where('user_id', $this->user->id)->get();
    expect($conversations)->toHaveCount(1);

    // Should have 4 messages (2 user + 2 assistant)
    expect(AssistantMessage::count())->toBe(4);
});

test('assistant status endpoint returns correct structure', function () {
    $response = $this->actingAs($this->user)
        ->getJson('/assistant/status');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'status' => [
                'available',
                'usage',
                'features',
            ],
        ]);
});

test('demo accounts cannot use assistant', function () {
    // Create a demo user with demo email
    $demoUser = User::factory()->create([
        'tenant_id' => $this->tenant->id,
        'email' => 'admin@konveksi.com',
    ]);

    $response = $this->actingAs($demoUser)
        ->postJson('/assistant/message', ['message' => 'Hello']);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'type' => 'demo_account',
        ]);

    expect($response->json('message'))->toContain('akun demo');
});

test('expired subscription cannot use assistant', function () {
    // Set tenant subscription as expired
    $this->tenant->update([
        'subscription_expires_at' => now()->subDay(),
    ]);

    $response = $this->actingAs($this->user)
        ->postJson('/assistant/message', ['message' => 'Hello']);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'type' => 'subscription_expired',
        ]);

    expect($response->json('message'))->toContain('Masa aktif langganan');
});

test('active subscription can use assistant', function () {
    // Ensure tenant has active subscription
    $this->tenant->update([
        'is_active' => true,
        'subscription_expires_at' => now()->addMonth(),
    ]);

    $response = $this->actingAs($this->user)
        ->postJson('/assistant/message', ['message' => 'Hello']);

    $response->assertSuccessful();
});
