<?php

namespace Tests\Unit;

use App\Models\SubscriptionPayment;
use App\Models\Tenant;
use App\Services\Subscription\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionServiceTest extends TestCase
{
    use RefreshDatabase;

    private SubscriptionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SubscriptionService();
    }

    public function test_get_plan_price_calculates_server_pricing(): void
    {
        expect($this->service->getPlanPrice('monthly'))->toBe(25000);
        expect($this->service->getPlanPrice('yearly'))->toBe(250000);
        expect($this->service->getPlanDurationMonths('monthly'))->toBe(1);
        expect($this->service->getPlanDurationMonths('yearly'))->toBe(12);
    }

    public function test_activates_subscription_for_new_tenant(): void
    {
        $tenant = Tenant::factory()->create([
            'subscription_plan' => 'trial',
            'subscription_expires_at' => null,
            'is_active' => false,
        ]);

        $payment = SubscriptionPayment::create([
            'tenant_id' => $tenant->id,
            'amount' => 25000,
            'status' => 'pending',
            'plan_type' => 'monthly',
            'duration_months' => 1,
            'payment_method' => 'sumopod',
        ]);

        $updated = $this->service->activateSubscription($payment);

        expect($updated->status)->toBe('approved');
        expect($updated->paid_at)->not->toBeNull();

        $tenant->refresh();
        expect($tenant->subscription_plan)->toBe('full');
        expect($tenant->is_active)->toBeTrue();
        expect($tenant->subscription_expires_at)->not->toBeNull();
        expect($tenant->subscription_expires_at->isFuture())->toBeTrue();
    }

    public function test_extends_existing_active_subscription(): void
    {
        $futureExpiry = now()->addMonths(2);
        $tenant = Tenant::factory()->create([
            'subscription_plan' => 'full',
            'subscription_expires_at' => $futureExpiry,
            'is_active' => true,
        ]);

        $payment = SubscriptionPayment::create([
            'tenant_id' => $tenant->id,
            'amount' => 25000,
            'status' => 'pending',
            'plan_type' => 'monthly',
            'duration_months' => 1,
            'payment_method' => 'sumopod',
        ]);

        $this->service->activateSubscription($payment);

        $tenant->refresh();
        // New expiry should be ~3 months from now (futureExpiry + 1 month)
        expect((int) $tenant->subscription_expires_at->diffInDays($futureExpiry->copy()->addMonth()))->toBe(0);
    }

    public function test_idempotent_activation_does_not_double_extend(): void
    {
        $futureExpiry = now()->addMonths(1);
        $tenant = Tenant::factory()->create([
            'subscription_plan' => 'full',
            'subscription_expires_at' => $futureExpiry,
            'is_active' => true,
        ]);

        $payment = SubscriptionPayment::create([
            'tenant_id' => $tenant->id,
            'amount' => 25000,
            'status' => 'pending',
            'plan_type' => 'monthly',
            'duration_months' => 1,
            'payment_method' => 'sumopod',
        ]);

        // First activation
        $this->service->activateSubscription($payment);
        $firstExpiry = $tenant->fresh()->subscription_expires_at;

        // Second activation call with same payment
        $this->service->activateSubscription($payment);
        $secondExpiry = $tenant->fresh()->subscription_expires_at;

        expect($firstExpiry->toDateTimeString())->toBe($secondExpiry->toDateTimeString());
    }

    public function test_mark_failed_does_not_alter_active_subscription(): void
    {
        $futureExpiry = now()->addMonth();
        $tenant = Tenant::factory()->create([
            'subscription_plan' => 'full',
            'subscription_expires_at' => $futureExpiry,
            'is_active' => true,
        ]);

        $payment = SubscriptionPayment::create([
            'tenant_id' => $tenant->id,
            'amount' => 25000,
            'status' => 'pending',
            'plan_type' => 'monthly',
            'duration_months' => 1,
            'payment_method' => 'sumopod',
        ]);

        $this->service->markFailed($payment, 'payment.failed', ['reason' => 'insufficient_funds']);

        $payment->refresh();
        expect($payment->status)->toBe('failed');

        $tenant->refresh();
        expect($tenant->subscription_plan)->toBe('full');
        expect($tenant->subscription_expires_at->toDateTimeString())->toBe($futureExpiry->toDateTimeString());
    }
}
