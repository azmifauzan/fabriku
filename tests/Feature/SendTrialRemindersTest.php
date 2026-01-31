<?php

use App\Mail\TrialReminderEmail;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\freezeTime;

test('trial reminder is sent 7 days before expiration', function () {
    Mail::fake();

    freezeTime();

    $tenant = Tenant::factory()->create([
        'subscription_plan' => 'trial',
        'subscription_expires_at' => now()->addDays(7),
        'is_active' => true,
    ]);

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'role' => 'admin',
    ]);

    Artisan::call('trial:send-reminders');

    Mail::assertQueued(TrialReminderEmail::class, function ($mail) use ($user, $tenant) {
        return $mail->hasTo($user->email) &&
               $mail->tenant->id === $tenant->id &&
               $mail->daysLeft === 7;
    });

    expect($tenant->fresh()->trial_reminder_7days_sent_at)->not->toBeNull();
});

test('trial reminder is sent 3 days before expiration', function () {
    Mail::fake();

    freezeTime();

    $tenant = Tenant::factory()->create([
        'subscription_plan' => 'trial',
        'subscription_expires_at' => now()->addDays(3),
        'is_active' => true,
    ]);

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'role' => 'admin',
    ]);

    Artisan::call('trial:send-reminders');

    Mail::assertQueued(TrialReminderEmail::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email) && $mail->daysLeft === 3;
    });

    expect($tenant->fresh()->trial_reminder_3days_sent_at)->not->toBeNull();
});

test('trial reminder is sent 1 day before expiration', function () {
    Mail::fake();

    freezeTime();

    $tenant = Tenant::factory()->create([
        'subscription_plan' => 'trial',
        'subscription_expires_at' => now()->addDay(),
        'is_active' => true,
    ]);

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'role' => 'admin',
    ]);

    Artisan::call('trial:send-reminders');

    Mail::assertQueued(TrialReminderEmail::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email) && $mail->daysLeft === 1;
    });

    expect($tenant->fresh()->trial_reminder_1day_sent_at)->not->toBeNull();
});

test('trial reminder is not sent twice for same period', function () {
    Mail::fake();

    freezeTime();

    $tenant = Tenant::factory()->create([
        'subscription_plan' => 'trial',
        'subscription_expires_at' => now()->addDays(7),
        'trial_reminder_7days_sent_at' => now(),
        'is_active' => true,
    ]);

    User::factory()->create([
        'tenant_id' => $tenant->id,
        'role' => 'admin',
    ]);

    Artisan::call('trial:send-reminders');

    Mail::assertNothingQueued();
});

test('trial reminder is not sent for expired trials', function () {
    Mail::fake();

    freezeTime();

    $tenant = Tenant::factory()->create([
        'subscription_plan' => 'trial',
        'subscription_expires_at' => now()->subDay(),
        'is_active' => true,
    ]);

    User::factory()->create([
        'tenant_id' => $tenant->id,
        'role' => 'admin',
    ]);

    Artisan::call('trial:send-reminders');

    Mail::assertNothingQueued();
});

test('trial reminder is not sent for inactive tenants', function () {
    Mail::fake();

    freezeTime();

    $tenant = Tenant::factory()->create([
        'subscription_plan' => 'trial',
        'subscription_expires_at' => now()->addDays(7),
        'is_active' => false,
    ]);

    User::factory()->create([
        'tenant_id' => $tenant->id,
        'role' => 'admin',
    ]);

    Artisan::call('trial:send-reminders');

    Mail::assertNothingQueued();
});

test('trial reminder is not sent for paid subscriptions', function () {
    Mail::fake();

    freezeTime();

    $tenant = Tenant::factory()->create([
        'subscription_plan' => 'monthly',
        'subscription_expires_at' => now()->addDays(7),
        'is_active' => true,
    ]);

    User::factory()->create([
        'tenant_id' => $tenant->id,
        'role' => 'admin',
    ]);

    Artisan::call('trial:send-reminders');

    Mail::assertNothingQueued();
});
