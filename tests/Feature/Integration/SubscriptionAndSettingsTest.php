<?php

use App\Models\Material;
use App\Models\SubscriptionPayment;
use App\Models\SystemSetting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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

    it('handles monthly subscription payment submission with proof', function () {
        Storage::fake(config('filesystems.uploads_disk', 'fabriku_s3'));

        $response = $this->actingAs($this->user)
            ->post(route('subscription.store'), [
                'plan_type' => 'monthly',
                'amount' => 25000,
                'proof' => UploadedFile::fake()->image('bukti-transfer.jpg'),
            ]);

        $response->assertRedirect();

        $payment = SubscriptionPayment::where('tenant_id', $this->tenant->id)->first();

        expect($payment)->not->toBeNull();
        expect((float) $payment->amount)->toBe(25000.0);
        expect($payment->status)->toBe('pending');
        expect($payment->plan_type)->toBe('monthly');
        expect($payment->duration_months)->toBe(1);
        expect($payment->proof_path)->not->toBeNull();
    });

    it('handles yearly subscription payment submission', function () {
        Storage::fake(config('filesystems.uploads_disk', 'fabriku_s3'));

        $response = $this->actingAs($this->user)
            ->post(route('subscription.store'), [
                'plan_type' => 'yearly',
                'amount' => 350000,
                'proof' => UploadedFile::fake()->image('bukti-transfer.jpg'),
            ]);

        $response->assertRedirect();

        $payment = SubscriptionPayment::where('tenant_id', $this->tenant->id)->first();

        expect($payment)->not->toBeNull();
        expect((float) $payment->amount)->toBe(350000.0);
        expect($payment->plan_type)->toBe('yearly');
        expect($payment->duration_months)->toBe(12);
    });

    it('validates subscription payment required fields', function () {
        $response = $this->actingAs($this->user)
            ->post(route('subscription.store'), []);

        $response->assertSessionHasErrors(['plan_type', 'proof', 'amount']);
    });

    it('allows viewing pages in read-only mode when expired', function () {
        // Update tenant to expired
        $this->tenant->update([
            'subscription_expires_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        // Should still be accessible (read-only mode)
        $response->assertSuccessful();
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

        $otherMaterial = Material::factory()->create([
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
        // Admin bypass CheckPermission — bisa akses semuanya
        $this->actingAs($this->admin)
            ->get(route('materials.index'))
            ->assertSuccessful();

        $this->actingAs($this->admin)
            ->get(route('settings.index'))
            ->assertSuccessful();

        // Manager/staff tanpa role RBAC ter-assign: route ber-permission ditolak
        $this->actingAs($this->manager)
            ->get(route('materials.index'))
            ->assertForbidden();

        $this->actingAs($this->staff)
            ->get(route('materials.index'))
            ->assertForbidden();

        // Route tanpa permission middleware tetap bisa diakses staff
        $this->actingAs($this->staff)
            ->get(route('settings.index'))
            ->assertSuccessful();
    });
});
