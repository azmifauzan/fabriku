<?php

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('email verification screen can be rendered', function () {
    $user = User::factory()->unverified()->create();

    actingAs($user);

    get(route('verification.notice'))
        ->assertSuccessful();
});

test('email can be verified', function () {
    Event::fake();

    $user = User::factory()->unverified()->create();

    actingAs($user);

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    get($verificationUrl)
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->component('Auth/EmailVerified'));

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();

    Event::assertDispatched(Verified::class);
});

test('email is not verified with invalid hash', function () {
    $user = User::factory()->unverified()->create();

    actingAs($user);

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1('wrong-email')]
    );

    get($verificationUrl);

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('email verification notification can be resent', function () {
    $user = User::factory()->unverified()->create();

    actingAs($user);

    $this->post(route('verification.send'))
        ->assertSessionHas('success');
});
