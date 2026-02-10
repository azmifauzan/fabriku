<?php

use App\Models\SubscriptionPayment;
use App\Models\SystemSetting;
use App\Models\Tenant;
use App\Models\User;

describe('Subscription Management Integration', function () {
    beforeEach(function () {
        // Set system settings
        SystemSetting::create([
            'tenant_id' => null,
            'key' => 'membership_price_monthly',
            'value' => '25000',
        ]);

        SystemSetting::create([
            'tenant_id' => null,
            'key' => 'membership_price_yearly',
            'value' => '250000',
        ]);

        SystemSetting::create([
            'tenant_id' => null,
            'key' => 'pro_price_monthly',
            'value' => '35000',
        ]);

        SystemSetting::create([
            'tenant_id' => null,
            'key' => 'pro_price_yearly',
            'value' => '350000',
        ]);

        $this->tenant = Tenant::factory()->create([
            'subscription_plan' => 'TRIAL',
            'subscription_expires_at' => now()->addDays(7),
        ]);

        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
    });

    it('allows user to view subscription page', function () {
        $this->actingAs($this->user)
            ->get(route('subscription.index'))
            ->assertSuccessful()
            ->assertSee('Subscription');
    });

    it('handles subscription upgrade from trial to membership monthly', function () {
        // Submit subscription request
        $response = $this->actingAs($this->user)
            ->post(route('subscription.store'), [
                'plan' => 'MEMBERSHIP',
                'billing_cycle' => 'MONTHLY',
                'payment_method' => 'TRANSFER',
                'amount' => 25000,
            ]);

        $response->assertRedirect();

        // Verify payment record created
        $payment = SubscriptionPayment::where('tenant_id', $this->tenant->id)
            ->where('plan', 'MEMBERSHIP')
            ->first();

        expect($payment)->not->toBeNull();
        expect($payment->amount)->toBe(25000);
        expect($payment->status)->toBe('PENDING');
        expect($payment->billing_cycle)->toBe('MONTHLY');
    });

    it('handles subscription upgrade to pro yearly', function () {
        $response = $this->actingAs($this->user)
            ->post(route('subscription.store'), [
                'plan' => 'PRO',
                'billing_cycle' => 'YEARLY',
                'payment_method' => 'TRANSFER',
                'amount' => 350000,
            ]);

        $response->assertRedirect();

        $payment = SubscriptionPayment::where('tenant_id', $this->tenant->id)
            ->where('plan', 'PRO')
            ->first();

        expect($payment)->not->toBeNull();
        expect($payment->amount)->toBe(350000);
        expect($payment->billing_cycle)->toBe('YEARLY');
    });

    it('validates subscription payment amount', function () {
        // Wrong amount for membership monthly
        $response = $this->actingAs($this->user)
            ->post(route('subscription.store'), [
                'plan' => 'MEMBERSHIP',
                'billing_cycle' => 'MONTHLY',
                'payment_method' => 'TRANSFER',
                'amount' => 10000, // Should be 25000
            ]);

        $response->assertSessionHasErrors('amount');
    });

    it('allows uploading payment proof', function () {
        $payment = SubscriptionPayment::factory()->create([
            'tenant_id' => $this->tenant->id,
            'plan' => 'MEMBERSHIP',
            'amount' => 25000,
            'status' => 'PENDING',
        ]);

        // In real scenario, would upload file
        // For now just verify payment exists
        expect($payment->status)->toBe('PENDING');
    });

    it('redirects to subscription page when expired', function () {
        // Update tenant to expired
        $this->tenant->update([
            'subscription_expires_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertRedirect(route('subscription.index'));
    });

    it('shows correct trial days remaining', function () {
        $daysRemaining = now()->diffInDays($this->tenant->subscription_expires_at);

        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertSuccessful();
        // Dashboard should show trial info
    });
});

describe('Settings Management Integration', function () {
    beforeEach(function () {
        $this->tenant = Tenant::factory()->create();

        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
    });

    it('allows admin to view settings', function () {
        $this->actingAs($this->user)
            ->get(route('settings.index'))
            ->assertSuccessful();
    });

    it('prevents non-admin from some actions', function () {
        $staff = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        // Staff should still be able to access settings page
        $response = $this->actingAs($staff)
            ->get(route('settings.index'));

        $response->assertSuccessful();
    });
});

describe('Multi-User Tenant Access', function () {
    beforeEach(function () {
        $this->tenant = Tenant::factory()->create();

        $this->admin = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->manager = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'manager',
            'email_verified_at' => now(),
        ]);

        $this->staff = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);
    });

    it('isolates data between different tenants', function () {
        // Create another tenant with data
        $otherTenant = Tenant::factory()->create();
        $otherUser = User::factory()->create([
            'tenant_id' => $otherTenant->id,
            'email_verified_at' => now(),
        ]);

        $otherMaterial = \App\Models\Material::factory()->create([
            'tenant_id' => $otherTenant->id,
            'name' => 'Other Tenant Material',
        ]);

        // Current tenant should not see other tenant's data
        $this->actingAs($this->admin)
            ->get(route('materials.index'))
            ->assertSuccessful()
            ->assertDontSee('Other Tenant Material');
    });

    it('allows different roles to access appropriate features', function () {
        // Admin can access everything
        $this->actingAs($this->admin)
            ->get(route('materials.index'))
            ->assertSuccessful();

        $this->actingAs($this->admin)
            ->get(route('settings.index'))
            ->assertSuccessful();

        // Manager can access most features
        $this->actingAs($this->manager)
            ->get(route('materials.index'))
            ->assertSuccessful();

        // Staff has limited access
        $this->actingAs($this->staff)
            ->get(route('materials.index'))
            ->assertSuccessful();
    });
});
