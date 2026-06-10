<?php

use App\Models\AssistantConversation;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Http;

describe('AI Assistant Integration', function () {
    beforeEach(function () {
        $this->tenant = Tenant::factory()->create([
            'subscription_plan' => 'PRO',
            'subscription_expires_at' => now()->addMonth(),
        ]);

        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'email_verified_at' => now(),
        ]);
    });

    it('allows user to start conversation with assistant', function () {
        $response = $this->actingAs($this->user)
            ->post(route('assistant.message'), [
                'message' => 'Berapa total stok material saya?',
            ]);

        $response->assertSuccessful();

        // Verify conversation created
        $conversation = AssistantConversation::where('tenant_id', $this->tenant->id)->first();
        expect($conversation)->not->toBeNull();
    });

    it('tracks conversation history', function () {
        // Create conversation
        $conversation = AssistantConversation::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'channel' => 'web',
            'status' => 'active',
        ]);

        // Add messages
        $conversation->messages()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'user',
            'content' => 'Apa laporan penjualan bulan ini?',
        ]);

        $conversation->messages()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'assistant',
            'content' => 'Berikut adalah laporan penjualan bulan ini...',
        ]);

        // Get history
        $response = $this->actingAs($this->user)
            ->get(route('assistant.history'));

        $response->assertSuccessful();
    });

    it('tracks assistant usage', function () {
        $response = $this->actingAs($this->user)
            ->get(route('assistant.usage'));

        $response->assertSuccessful();
    });

    it('allows clearing conversation history', function () {
        // Create conversation
        $conversation = AssistantConversation::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'channel' => 'web',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('assistant.clear'));

        $response->assertSuccessful();
    });

    it('checks assistant availability status', function () {
        $response = $this->actingAs($this->user)
            ->get(route('assistant.status'));

        $response->assertSuccessful();
    });

    it('keeps assistant accessible regardless of plan while subscription active', function () {
        $this->tenant->update([
            'subscription_plan' => 'MEMBERSHIP',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('assistant.message'), [
                'message' => 'Hello',
            ]);

        // Tidak ada gating per plan — fitur hanya disembunyikan di UI
        $response->assertSuccessful();
    });
});

describe('Telegram Integration', function () {
    beforeEach(function () {
        $this->tenant = Tenant::factory()->create();

        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
    });

    it('allows generating telegram connection token', function () {
        $response = $this->actingAs($this->user)
            ->post(route('telegram.generate-token'));

        $response->assertRedirect()->assertSessionHas('success');

        $this->user->refresh();
        expect($this->user->telegram_connect_token)->not->toBeNull();
        expect($this->user->telegram_connect_token_expires_at)->not->toBeNull();
    });

    it('allows disconnecting telegram', function () {
        // Set telegram connection
        $this->user->update([
            'telegram_chat_id' => '123456789',
        ]);

        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);

        $response = $this->actingAs($this->user)
            ->post(route('telegram.disconnect'));

        $response->assertRedirect()->assertSessionHas('success');

        $this->user->refresh();
        expect($this->user->telegram_chat_id)->toBeNull();
    });

    it('allows testing telegram message', function () {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);

        // Set telegram connection
        $this->user->update([
            'telegram_chat_id' => '123456789',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('telegram.test'));

        $response->assertRedirect();
    });

    it('prevents telegram features without connection', function () {
        $response = $this->actingAs($this->user)
            ->post(route('telegram.test'));

        $response->assertRedirect()->assertSessionHas('error');
    });

    it('expires old telegram tokens', function () {
        $this->user->update([
            'telegram_connect_token' => 'old-token',
            'telegram_connect_token_expires_at' => now()->subHour(),
        ]);

        // Try to generate new token
        $response = $this->actingAs($this->user)
            ->post(route('telegram.generate-token'));

        $response->assertRedirect()->assertSessionHas('success');

        $this->user->refresh();
        expect($this->user->telegram_connect_token)->not->toBe('old-token');
    });
});

describe('Report Generation Integration', function () {
    beforeEach(function () {
        $this->tenant = Tenant::factory()->create();

        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'email_verified_at' => now(),
        ]);
    });

    it('allows viewing material report', function () {
        $response = $this->actingAs($this->user)
            ->get(route('reports.material'));

        $response->assertSuccessful();
    });

    it('allows exporting material report to excel', function () {
        $response = $this->actingAs($this->user)
            ->get(route('reports.material.export'));

        $response->assertSuccessful();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    });

    it('allows viewing inventory report', function () {
        $response = $this->actingAs($this->user)
            ->get(route('reports.inventory'));

        $response->assertSuccessful();
    });

    it('allows exporting inventory report', function () {
        $response = $this->actingAs($this->user)
            ->get(route('reports.inventory.export'));

        $response->assertSuccessful();
    });

    it('allows viewing sales report', function () {
        $response = $this->actingAs($this->user)
            ->get(route('reports.sales'));

        $response->assertSuccessful();
    });

    it('allows exporting sales report', function () {
        $response = $this->actingAs($this->user)
            ->get(route('reports.sales.export'));

        $response->assertSuccessful();
    });

    it('allows viewing production report', function () {
        $response = $this->actingAs($this->user)
            ->get(route('reports.production'));

        $response->assertSuccessful();
    });

    it('allows exporting production report', function () {
        $response = $this->actingAs($this->user)
            ->get(route('reports.production.export'));

        $response->assertSuccessful();
    });

    it('allows filtering reports by date range', function () {
        $response = $this->actingAs($this->user)
            ->get(route('reports.sales', [
                'start_date' => now()->startOfMonth()->format('Y-m-d'),
                'end_date' => now()->endOfMonth()->format('Y-m-d'),
            ]));

        $response->assertSuccessful();
    });

    it('allows viewing sales recap report', function () {
        $response = $this->actingAs($this->user)
            ->get(route('reports.sales-recap'));

        $response->assertSuccessful();
    });

    it('allows exporting sales recap', function () {
        $response = $this->actingAs($this->user)
            ->get(route('reports.sales-recap.export'));

        $response->assertSuccessful();
    });
});
