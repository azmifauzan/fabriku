<?php

use App\Models\SubscriptionPayment;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->internalSecret = 'test_fab_internal_secret_12345';

    config([
        'services.sumopod.internal_secret' => $this->internalSecret,
        'services.sumopod.environment' => 'live',
    ]);

    $this->tenant = Tenant::factory()->create([
        'subscription_plan' => 'trial',
        'subscription_expires_at' => null,
        'is_active' => false,
    ]);
});

function generateFabrikuHeaders(string $secret, string $webhookId, int $timestamp, string $env, string $rawBody): array
{
    $sig = 'v1,'.base64_encode(hash_hmac('sha256', "{$webhookId}.{$timestamp}.{$rawBody}", $secret, true));

    return [
        'X-Webhook-Id' => $webhookId,
        'X-Webhook-Timestamp' => (string) $timestamp,
        'X-Webhook-Environment' => $env,
        'X-Webhook-Signature' => $sig,
    ];
}

test('internal sumopod webhook activates tenant subscription and completes payment', function () {
    $payment = SubscriptionPayment::create([
        'tenant_id' => $this->tenant->id,
        'amount' => 25000,
        'status' => 'pending',
        'plan_type' => 'monthly',
        'duration_months' => 1,
        'payment_method' => 'sumopod',
        'provider' => 'sumopod',
        'provider_order_id' => 'FAB-SUB-101',
        'provider_payment_id' => 'pay-fab-uuid-001',
    ]);

    $payload = [
        'event_type' => 'payment.completed',
        'data' => [
            'payment_id' => 'pay-fab-uuid-001',
            'order_id' => 'FAB-SUB-101',
            'amount' => 25000,
            'fee' => 500,
            'net_amount' => 25000,
            'status' => 'completed',
        ],
    ];

    $rawBody = json_encode($payload);
    $headers = generateFabrikuHeaders($this->internalSecret, 'msg_fab_001', time(), 'live', $rawBody);

    $response = $this->call(
        'POST',
        '/internal/webhooks/sumopod',
        [],
        [],
        [],
        $this->transformHeadersToServerVars($headers),
        $rawBody
    );

    $response->assertStatus(200);
    $response->assertJson(['ok' => true, 'status' => 'approved']);

    $payment->refresh();
    expect($payment->status)->toBe('approved');
    expect($payment->paid_at)->not->toBeNull();

    $this->tenant->refresh();
    expect($this->tenant->subscription_plan)->toBe('full');
    expect($this->tenant->is_active)->toBeTrue();
    expect($this->tenant->subscription_expires_at)->not->toBeNull();
});

test('duplicate internal webhook returns 200 already_approved without double extending', function () {
    $payment = SubscriptionPayment::create([
        'tenant_id' => $this->tenant->id,
        'amount' => 25000,
        'status' => 'pending',
        'plan_type' => 'monthly',
        'duration_months' => 1,
        'payment_method' => 'sumopod',
        'provider' => 'sumopod',
        'provider_order_id' => 'FAB-SUB-102',
        'provider_payment_id' => 'pay-fab-uuid-002',
    ]);

    $payload = [
        'event_type' => 'payment.completed',
        'data' => [
            'payment_id' => 'pay-fab-uuid-002',
            'order_id' => 'FAB-SUB-102',
            'amount' => 25000,
            'status' => 'completed',
        ],
    ];

    $rawBody = json_encode($payload);
    $headers = generateFabrikuHeaders($this->internalSecret, 'msg_fab_002', time(), 'live', $rawBody);

    // First call
    $this->call('POST', '/internal/webhooks/sumopod', [], [], [], $this->transformHeadersToServerVars($headers), $rawBody)
        ->assertStatus(200)
        ->assertJson(['ok' => true, 'status' => 'approved']);

    $firstExpiry = $this->tenant->fresh()->subscription_expires_at;

    // Second call
    $this->call('POST', '/internal/webhooks/sumopod', [], [], [], $this->transformHeadersToServerVars($headers), $rawBody)
        ->assertStatus(200)
        ->assertJson(['ok' => true, 'status' => 'already_approved']);

    $secondExpiry = $this->tenant->fresh()->subscription_expires_at;
    expect($firstExpiry->toDateTimeString())->toBe($secondExpiry->toDateTimeString());
});

test('internal sumopod webhook rejects invalid signature in fabriku', function () {
    $payload = [
        'event_type' => 'payment.completed',
        'data' => [
            'payment_id' => 'pay-uuid-003',
            'order_id' => 'FAB-SUB-103',
            'amount' => 25000,
        ],
    ];

    $rawBody = json_encode($payload);
    $headers = generateFabrikuHeaders('wrong_secret', 'msg_fab_003', time(), 'live', $rawBody);

    $this->call('POST', '/internal/webhooks/sumopod', [], [], [], $this->transformHeadersToServerVars($headers), $rawBody)
        ->assertStatus(401);
});

test('internal sumopod webhook rejects amount mismatch with 422 in fabriku', function () {
    $payment = SubscriptionPayment::create([
        'tenant_id' => $this->tenant->id,
        'amount' => 250000, // Expected yearly price
        'status' => 'pending',
        'plan_type' => 'yearly',
        'duration_months' => 12,
        'payment_method' => 'sumopod',
        'provider' => 'sumopod',
        'provider_order_id' => 'FAB-SUB-104',
        'provider_payment_id' => 'pay-fab-uuid-004',
    ]);

    $payload = [
        'event_type' => 'payment.completed',
        'data' => [
            'payment_id' => 'pay-fab-uuid-004',
            'order_id' => 'FAB-SUB-104',
            'amount' => 25000, // Mismatch: 25k instead of 250k!
            'status' => 'completed',
        ],
    ];

    $rawBody = json_encode($payload);
    $headers = generateFabrikuHeaders($this->internalSecret, 'msg_fab_004', time(), 'live', $rawBody);

    $this->call('POST', '/internal/webhooks/sumopod', [], [], [], $this->transformHeadersToServerVars($headers), $rawBody)
        ->assertStatus(422)
        ->assertJson(['error' => 'Amount mismatch']);

    $payment->refresh();
    expect($payment->status)->toBe('pending');
    expect($this->tenant->fresh()->is_active)->toBeFalse();
});

test('internal sumopod webhook accepts gross amount when charge-fee-to-customer diverges from net amount', function () {
    // `amount` is the net/business amount (25000); `provider_amount` is SumoPod's own gross
    // amount captured at payment-creation time (25475, when "charge fee to customer" is on).
    $payment = SubscriptionPayment::create([
        'tenant_id' => $this->tenant->id,
        'amount' => 25000,
        'provider_amount' => 25475,
        'provider_fee' => 475,
        'status' => 'pending',
        'plan_type' => 'monthly',
        'duration_months' => 1,
        'payment_method' => 'sumopod',
        'provider' => 'sumopod',
        'provider_order_id' => 'FAB-SUB-107',
        'provider_payment_id' => 'pay-fab-uuid-007',
    ]);

    $payload = [
        'event_type' => 'payment.completed',
        'data' => [
            'payment_id' => 'pay-fab-uuid-007',
            'order_id' => 'FAB-SUB-107',
            'amount' => 25475,
            'fee' => 475,
            'net_amount' => 25000,
            'status' => 'completed',
        ],
    ];

    $rawBody = json_encode($payload);
    $headers = generateFabrikuHeaders($this->internalSecret, 'msg_fab_007', time(), 'live', $rawBody);

    $this->call('POST', '/internal/webhooks/sumopod', [], [], [], $this->transformHeadersToServerVars($headers), $rawBody)
        ->assertStatus(200)
        ->assertJson(['ok' => true, 'status' => 'approved']);

    $payment->refresh();
    expect($payment->status)->toBe('approved');
});

test('internal sumopod webhook still rejects a tampered amount when provider_amount is set', function () {
    $payment = SubscriptionPayment::create([
        'tenant_id' => $this->tenant->id,
        'amount' => 25000,
        'provider_amount' => 25475,
        'provider_fee' => 475,
        'status' => 'pending',
        'plan_type' => 'monthly',
        'duration_months' => 1,
        'payment_method' => 'sumopod',
        'provider' => 'sumopod',
        'provider_order_id' => 'FAB-SUB-108',
        'provider_payment_id' => 'pay-fab-uuid-008',
    ]);

    $payload = [
        'event_type' => 'payment.completed',
        'data' => [
            'payment_id' => 'pay-fab-uuid-008',
            'order_id' => 'FAB-SUB-108',
            'amount' => 999999, // tampered, matches neither amount nor provider_amount
            'status' => 'completed',
        ],
    ];

    $rawBody = json_encode($payload);
    $headers = generateFabrikuHeaders($this->internalSecret, 'msg_fab_008', time(), 'live', $rawBody);

    $this->call('POST', '/internal/webhooks/sumopod', [], [], [], $this->transformHeadersToServerVars($headers), $rawBody)
        ->assertStatus(422)
        ->assertJson(['error' => 'Amount mismatch']);

    $payment->refresh();
    expect($payment->status)->toBe('pending');
});

test('internal sumopod webhook rejects environment mismatch with 403 in fabriku', function () {
    $payload = [
        'event_type' => 'payment.completed',
        'data' => [
            'payment_id' => 'pay-uuid-005',
            'order_id' => 'FAB-SUB-105',
            'amount' => 25000,
        ],
    ];

    $rawBody = json_encode($payload);
    $headers = generateFabrikuHeaders($this->internalSecret, 'msg_fab_005', time(), 'sandbox', $rawBody);

    $this->call('POST', '/internal/webhooks/sumopod', [], [], [], $this->transformHeadersToServerVars($headers), $rawBody)
        ->assertStatus(403)
        ->assertJson(['error' => 'Environment mismatch']);
});

test('internal sumopod webhook marks payment failed on payment.failed', function () {
    $payment = SubscriptionPayment::create([
        'tenant_id' => $this->tenant->id,
        'amount' => 25000,
        'status' => 'pending',
        'plan_type' => 'monthly',
        'duration_months' => 1,
        'payment_method' => 'sumopod',
        'provider' => 'sumopod',
        'provider_order_id' => 'FAB-SUB-106',
        'provider_payment_id' => 'pay-fab-uuid-006',
    ]);

    $payload = [
        'event_type' => 'payment.failed',
        'data' => [
            'payment_id' => 'pay-fab-uuid-006',
            'order_id' => 'FAB-SUB-106',
            'amount' => 25000,
            'status' => 'failed',
        ],
    ];

    $rawBody = json_encode($payload);
    $headers = generateFabrikuHeaders($this->internalSecret, 'msg_fab_006', time(), 'live', $rawBody);

    $this->call('POST', '/internal/webhooks/sumopod', [], [], [], $this->transformHeadersToServerVars($headers), $rawBody)
        ->assertStatus(200)
        ->assertJson(['ok' => true, 'status' => 'failed']);

    $payment->refresh();
    expect($payment->status)->toBe('failed');
    expect($this->tenant->fresh()->is_active)->toBeFalse();
});

test('internal sumopod webhook cannot mutate a manual payment', function () {
    $payment = SubscriptionPayment::create([
        'tenant_id' => $this->tenant->id,
        'amount' => 25000,
        'status' => 'pending',
        'plan_type' => 'monthly',
        'duration_months' => 1,
        'payment_method' => 'manual',
        'provider_order_id' => 'FAB-SUB-MANUAL',
    ]);
    $payload = [
        'event_type' => 'payment.failed',
        'data' => ['payment_id' => 'pay-manual', 'order_id' => 'FAB-SUB-MANUAL', 'amount' => 25000],
    ];
    $rawBody = json_encode($payload);
    $headers = generateFabrikuHeaders($this->internalSecret, 'msg_fab_manual', time(), 'live', $rawBody);

    $this->call('POST', '/internal/webhooks/sumopod', [], [], [], $this->transformHeadersToServerVars($headers), $rawBody)
        ->assertStatus(404);

    expect($payment->fresh()->status)->toBe('pending');
});
