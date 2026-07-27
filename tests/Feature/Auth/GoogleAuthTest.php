<?php

use App\Models\Tenant;
use App\Models\User;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

function fakeGoogleUser(array $attributes = []): SocialiteUser
{
    return SocialiteUser::fake(array_merge(['email_verified' => true], $attributes));
}

function mockGoogleProvider(SocialiteUser $user): void
{
    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('user')->andReturn($user);

    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
}

describe('Google OAuth', function () {
    it('redirects to google', function () {
        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('redirect')->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $this->get(route('google.redirect'))->assertRedirect();
    });

    it('sends a brand new google email to the completion form', function () {
        mockGoogleProvider(fakeGoogleUser([
            'id' => 'g-123',
            'email' => 'newbie@example.com',
            'name' => 'New Bie',
        ]));

        $this->get(route('google.callback'))
            ->assertRedirect(route('google.complete'));

        expect(User::where('email', 'newbie@example.com')->exists())->toBeFalse();

        $this->get(route('google.complete'))->assertSuccessful();

        $this->post(route('google.complete.store'), [
            'business_name' => 'Toko Bie',
            'business_category' => 'retail',
            'name' => 'New Bie',
        ])->assertRedirect(route('dashboard'));

        $user = User::where('email', 'newbie@example.com')->first();
        expect($user)->not->toBeNull();
        expect($user->google_id)->toBe('g-123');
        expect($user->email_verified_at)->not->toBeNull();
        expect($user->tenant)->not->toBeNull();
        expect($user->tenant->business_category)->toBe('retail');

        $this->assertAuthenticatedAs($user);
    });

    it('logs in an existing user matched by google_id', function () {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'google_id' => 'g-999',
            'email' => 'known@example.com',
            'email_verified_at' => now(),
        ]);

        mockGoogleProvider(fakeGoogleUser([
            'id' => 'g-999',
            'email' => 'known@example.com',
            'name' => 'Known User',
        ]));

        $this->get(route('google.callback'))
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    });

    it('auto-links an existing password account by matching email', function () {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'google_id' => null,
            'email' => 'linkme@example.com',
            'email_verified_at' => null,
        ]);

        mockGoogleProvider(fakeGoogleUser([
            'id' => 'g-link',
            'email' => 'linkme@example.com',
            'name' => 'Link Me',
        ]));

        $this->get(route('google.callback'))
            ->assertRedirect(route('dashboard'));

        $user->refresh();
        expect($user->google_id)->toBe('g-link');
        expect($user->email_verified_at)->not->toBeNull();

        $this->assertAuthenticatedAs($user);
    });

    it('refuses to auto-link an existing account when Google has not verified the email', function () {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'google_id' => null,
            'email' => 'victim@example.com',
            'email_verified_at' => null,
        ]);

        mockGoogleProvider(fakeGoogleUser([
            'id' => 'g-attacker',
            'email' => 'victim@example.com',
            'name' => 'Attacker',
            'email_verified' => false,
        ]));

        $this->get(route('google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $user->refresh();
        expect($user->google_id)->toBeNull();
        expect($user->email_verified_at)->toBeNull();

        $this->assertGuest();
    });
});
