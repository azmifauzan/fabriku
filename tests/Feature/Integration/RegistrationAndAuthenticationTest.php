<?php

use App\Models\Tenant;
use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

describe('User Registration and Authentication Flow', function () {
    beforeEach(function () {
        Notification::fake();
    });

    it('allows user to register with complete flow', function () {
        // Step 1: Access registration page
        $response = $this->get(route('register'));
        $response->assertSuccessful();

        // Step 2: Submit registration form
        $userData = [
            'business_name' => 'Konveksi Sejahtera',
            'business_category' => 'GARMENT',
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '081234567890',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->post(route('register'), $userData);
        $response->assertRedirect(route('verification.notice'));

        // Step 3: Verify tenant created
        $tenant = Tenant::where('email', 'john@example.com')->first();
        expect($tenant)->not->toBeNull();
        expect($tenant->name)->toBe('Konveksi Sejahtera');
        expect($tenant->business_category)->toBe('GARMENT');
        expect($tenant->is_active)->toBeTrue();
        expect($tenant->subscription_plan)->toBe('TRIAL');
        expect($tenant->subscription_expires_at)->not->toBeNull();

        // Step 4: Verify user created
        $user = User::where('email', 'john@example.com')->first();
        expect($user)->not->toBeNull();
        expect($user->name)->toBe('John Doe');
        expect($user->tenant_id)->toBe($tenant->id);
        expect($user->role)->toBe('admin');
        expect($user->is_active)->toBeTrue();
        expect($user->email_verified_at)->toBeNull();

        // Step 5: Verify email notification sent
        Notification::assertSentTo($user, VerifyEmailNotification::class);

        // Step 6: Try to access dashboard without verification
        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('verification.notice'));

        // Step 7: Verify email
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->actingAs($user)
            ->get($verificationUrl)
            ->assertRedirect(route('dashboard'));

        // Step 8: Check email verified
        $user->refresh();
        expect($user->email_verified_at)->not->toBeNull();

        // Step 9: Access dashboard successfully
        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertSuccessful()
            ->assertSee('Dashboard');
    });

    it('handles login flow correctly', function () {
        // Create verified user
        $tenant = Tenant::factory()->create([
            'business_category' => 'FOOD',
        ]);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // Step 1: Access login page
        $this->get(route('login'))
            ->assertSuccessful();

        // Step 2: Submit login with wrong password
        $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ])->assertSessionHasErrors('email');

        // Step 3: Submit correct credentials
        $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'password123',
        ])->assertRedirect(route('dashboard'));

        // Step 4: Verify authenticated
        $this->assertAuthenticatedAs($user);

        // Step 5: Access protected route
        $this->get(route('dashboard'))
            ->assertSuccessful();

        // Step 6: Logout
        $this->post(route('logout'))
            ->assertRedirect(route('home'));

        // Step 7: Verify logged out
        $this->assertGuest();
    });

    it('handles password reset flow', function () {
        // Create user
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'email' => 'reset@example.com',
            'email_verified_at' => now(),
        ]);

        // Step 1: Access forgot password page
        $this->get(route('password.request'))
            ->assertSuccessful();

        // Step 2: Submit email
        $this->post(route('password.email'), [
            'email' => 'reset@example.com',
        ])->assertSessionHas('status');

        // Step 3: Verify reset link sent
        Notification::assertSentTo($user, \App\Notifications\ResetPasswordNotification::class);
    });

    it('enforces subscription check', function () {
        // Create tenant with expired subscription
        $tenant = Tenant::factory()->create([
            'subscription_plan' => 'TRIAL',
            'subscription_expires_at' => now()->subDay(),
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'email_verified_at' => now(),
        ]);

        // Try to access dashboard
        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('subscription.index'));
    });

    it('prevents suspended tenant access', function () {
        // Create suspended tenant
        $tenant = Tenant::factory()->create([
            'is_active' => false,
            'subscription_expires_at' => now()->addMonth(),
        ]);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'email_verified_at' => now(),
        ]);

        // Try to login
        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertForbidden();
    });

    it('validates registration data correctly', function () {
        // Test missing required fields
        $this->post(route('register'), [])
            ->assertSessionHasErrors([
                'business_name',
                'business_category',
                'name',
                'email',
                'password',
            ]);

        // Test invalid email
        $this->post(route('register'), [
            'business_name' => 'Test Business',
            'business_category' => 'GARMENT',
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHasErrors('email');

        // Test weak password
        $this->post(route('register'), [
            'business_name' => 'Test Business',
            'business_category' => 'GARMENT',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '123',
            'password_confirmation' => '123',
        ])->assertSessionHasErrors('password');

        // Test password mismatch
        $this->post(route('register'), [
            'business_name' => 'Test Business',
            'business_category' => 'GARMENT',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
        ])->assertSessionHasErrors('password');
    });

    it('handles resend verification email', function () {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'email_verified_at' => null,
        ]);

        $this->actingAs($user)
            ->post(route('verification.send'))
            ->assertSessionHas('status');

        Notification::assertSentTo($user, VerifyEmailNotification::class);
    });
});
