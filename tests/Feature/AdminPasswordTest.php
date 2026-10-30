<?php

use App\Models\AdminUser;
use Illuminate\Support\Facades\Hash;

function passwordAdmin(): AdminUser
{
    $admin = AdminUser::factory()->create(['password' => Hash::make('password')]);
    test()->actingAs($admin, 'admin');

    return $admin;
}

it('renders the change password page', function () {
    passwordAdmin();

    $this->get(route('admin.account.password.edit'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/Account/Password'));
});

it('changes the admin password', function () {
    $admin = passwordAdmin();

    $this->put(route('admin.account.password.update'), [
        'current_password' => 'password',
        'password' => 'kata-sandi-baru-123',
        'password_confirmation' => 'kata-sandi-baru-123',
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect(Hash::check('kata-sandi-baru-123', $admin->fresh()->password))->toBeTrue();
});

it('rejects a wrong current password', function () {
    $admin = passwordAdmin();

    $this->put(route('admin.account.password.update'), [
        'current_password' => 'salah',
        'password' => 'kata-sandi-baru-123',
        'password_confirmation' => 'kata-sandi-baru-123',
    ])->assertSessionHasErrors('current_password');

    expect(Hash::check('password', $admin->fresh()->password))->toBeTrue();
});

it('rejects short, unconfirmed, or unchanged passwords', function (array $payload, string $field) {
    passwordAdmin();

    $this->put(route('admin.account.password.update'), $payload + ['current_password' => 'password'])
        ->assertSessionHasErrors($field);
})->with([
    'too short' => [['password' => 'pendek1', 'password_confirmation' => 'pendek1'], 'password'],
    'not confirmed' => [['password' => 'kata-sandi-baru-123', 'password_confirmation' => 'lain'], 'password'],
    'same as current' => [['password' => 'password', 'password_confirmation' => 'password'], 'password'],
]);

it('keeps guests out', function () {
    $this->get(route('admin.account.password.edit'))->assertRedirect();
    $this->put(route('admin.account.password.update'), [])->assertRedirect();
});
