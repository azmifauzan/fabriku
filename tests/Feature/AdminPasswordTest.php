<?php

use App\Models\AdminUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->currentSecret = Str::password(16);
    $this->newSecret = Str::password(16);

    $this->admin = AdminUser::factory()->create(['password' => Hash::make($this->currentSecret)]);
    $this->actingAs($this->admin, 'admin');
});

it('renders the change password page', function () {
    $this->get(route('admin.account.password.edit'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/Account/Password'));
});

it('changes the admin password', function () {
    $this->put(route('admin.account.password.update'), [
        'current_password' => $this->currentSecret,
        'password' => $this->newSecret,
        'password_confirmation' => $this->newSecret,
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect(Hash::check($this->newSecret, $this->admin->fresh()->password))->toBeTrue();
});

it('rejects a wrong current password', function () {
    $this->put(route('admin.account.password.update'), [
        'current_password' => Str::password(16),
        'password' => $this->newSecret,
        'password_confirmation' => $this->newSecret,
    ])->assertSessionHasErrors('current_password');

    expect(Hash::check($this->currentSecret, $this->admin->fresh()->password))->toBeTrue();
});

it('rejects a password shorter than 12 characters', function () {
    $short = Str::password(8);

    $this->put(route('admin.account.password.update'), [
        'current_password' => $this->currentSecret,
        'password' => $short,
        'password_confirmation' => $short,
    ])->assertSessionHasErrors('password');
});

it('rejects an unconfirmed password', function () {
    $this->put(route('admin.account.password.update'), [
        'current_password' => $this->currentSecret,
        'password' => $this->newSecret,
        'password_confirmation' => Str::password(16),
    ])->assertSessionHasErrors('password');
});

it('rejects a password equal to the current one', function () {
    $this->put(route('admin.account.password.update'), [
        'current_password' => $this->currentSecret,
        'password' => $this->currentSecret,
        'password_confirmation' => $this->currentSecret,
    ])->assertSessionHasErrors('password');
});

it('keeps guests out', function () {
    auth('admin')->logout();

    $this->get(route('admin.account.password.edit'))->assertRedirect();
    $this->put(route('admin.account.password.update'), [])->assertRedirect();
});
