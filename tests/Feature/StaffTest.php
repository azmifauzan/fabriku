<?php

use App\Models\Role;
use App\Models\Staff;
use App\Models\Tenant;
use App\Models\User;

test('authenticated user can create staff', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'role' => 'admin',
    ]);

    $this->actingAs($user);

    $role = Role::create([
        'tenant_id' => $tenant->id,
        'name' => 'Staff Produksi',
        'slug' => 'staff-produksi',
    ]);

    $response = $this->post(route('staff.store'), [
        'code' => 'STF-TEST',
        'name' => 'Test Staff',
        'role_id' => $role->id,
        'phone' => '081234567890',
        'email' => 'test@example.com',
        'is_active' => true,
    ]);

    $response->assertRedirect(route('staff.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('staff', [
        'code' => 'STF-TEST',
        'name' => 'Test Staff',
        'tenant_id' => $tenant->id,
    ]);
});

test('staff can be updated', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'role' => 'admin',
    ]);
    $staff = Staff::factory()->create([
        'tenant_id' => $tenant->id,
        'code' => 'STF-OLD',
        'name' => 'Old Name',
    ]);

    $this->actingAs($user);

    $role = Role::create([
        'tenant_id' => $tenant->id,
        'name' => 'Supervisor',
        'slug' => 'supervisor',
    ]);

    $response = $this->put(route('staff.update', $staff), [
        'code' => 'STF-NEW',
        'name' => 'New Name',
        'role_id' => $role->id,
        'phone' => '081234567890',
        'email' => 'updated@example.com',
        'is_active' => true,
    ]);

    $response->assertRedirect(route('staff.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('staff', [
        'id' => $staff->id,
        'code' => 'STF-NEW',
        'name' => 'New Name',
    ]);
});

test('staff requires valid data', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'role' => 'admin',
    ]);

    $this->actingAs($user);

    $response = $this->post(route('staff.store'), [
        'code' => '', // Required field
        'name' => '', // Required field
    ]);

    $response->assertSessionHasErrors(['code', 'name']);
});

test('staff code must be unique within tenant', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'role' => 'admin',
    ]);
    $existingStaff = Staff::factory()->create([
        'tenant_id' => $tenant->id,
        'code' => 'STF-DUPLICATE',
    ]);

    $this->actingAs($user);

    $response = $this->post(route('staff.store'), [
        'code' => 'STF-DUPLICATE',
        'name' => 'New Staff',
        'is_active' => true,
    ]);

    $response->assertSessionHasErrors(['code']);
});

test('staff create form lists system roles and tenant custom roles', function () {
    $tenant = Tenant::factory()->create();
    $admin = User::factory()->create(['tenant_id' => $tenant->id, 'role' => 'admin']);

    Role::create(['tenant_id' => null, 'name' => 'Manager', 'slug' => 'manager', 'is_system_role' => true]);
    Role::create(['tenant_id' => null, 'name' => 'Tenant Admin', 'slug' => 'tenant_admin', 'is_system_role' => true]);
    Role::create(['tenant_id' => $tenant->id, 'name' => 'Kasir Gudang', 'slug' => 'kasir-gudang', 'is_system_role' => false]);

    $this->actingAs($admin);

    $response = $this->get(route('staff.create'));

    // Expect 2 roles: Manager (system, non-tenant_admin) and Kasir Gudang (custom)
    // Tenant Admin should be excluded by the slug != 'tenant_admin' filter
    $response->assertInertia(fn ($page) => $page
        ->component('Staff/Form')
        ->has('roles', 2)
    );
});
