<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\Staff;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

function makeRolePermission(string $slug, string $module): Permission
{
    return Permission::create([
        'name' => $slug,
        'slug' => $slug,
        'module' => $module,
        'description' => $slug,
    ]);
}

test('admin can create a custom role with permissions', function () {
    $tenant = Tenant::factory()->create();
    $admin = User::factory()->create(['tenant_id' => $tenant->id, 'role' => 'admin']);
    $materialView = makeRolePermission('material.view', 'material');
    $salesView = makeRolePermission('sales.view', 'sales');

    $this->actingAs($admin);

    $response = $this->post(route('roles.store'), [
        'name' => 'Kasir Gudang',
        'description' => 'Akses gudang & kasir',
        'permission_ids' => [$materialView->id, $salesView->id],
    ]);

    $response->assertRedirect(route('roles.index'))->assertSessionHas('success');

    $this->assertDatabaseHas('roles', [
        'tenant_id' => $tenant->id,
        'name' => 'Kasir Gudang',
        'slug' => 'kasir-gudang',
        'is_system_role' => false,
    ]);

    $role = Role::where('name', 'Kasir Gudang')->first();
    expect($role->permissions()->pluck('permissions.id')->sort()->values()->all())
        ->toBe(collect([$materialView->id, $salesView->id])->sort()->values()->all());
});

test('non-admin cannot access role management', function () {
    $tenant = Tenant::factory()->create();
    $staffUser = User::factory()->create(['tenant_id' => $tenant->id, 'role' => 'staff']);
    $permission = makeRolePermission('material.view.nonAdmin', 'material');

    $this->actingAs($staffUser);

    $this->get(route('roles.index'))->assertForbidden();
    $this->get(route('roles.create'))->assertForbidden();
    $this->post(route('roles.store'), [
        'name' => 'Test',
        'permission_ids' => [$permission->id],
    ])->assertForbidden();
});

test('store rejects a permission from the tenant module', function () {
    $tenant = Tenant::factory()->create();
    $admin = User::factory()->create(['tenant_id' => $tenant->id, 'role' => 'admin']);
    $tenantPermission = makeRolePermission('tenant.view', 'tenant');

    $this->actingAs($admin);

    $response = $this->post(route('roles.store'), [
        'name' => 'Sneaky Role',
        'permission_ids' => [$tenantPermission->id],
    ]);

    $response->assertSessionHasErrors(['permission_ids.0']);
    $this->assertDatabaseMissing('roles', ['name' => 'Sneaky Role']);
});

test('duplicate role names across tenants do not collide on slug', function () {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();
    $adminA = User::factory()->create(['tenant_id' => $tenantA->id, 'role' => 'admin']);
    $adminB = User::factory()->create(['tenant_id' => $tenantB->id, 'role' => 'admin']);
    $permission = makeRolePermission('material.view.dup', 'material');

    $this->actingAs($adminA);
    $this->post(route('roles.store'), ['name' => 'Kasir', 'permission_ids' => [$permission->id]]);

    $this->actingAs($adminB);
    $this->post(route('roles.store'), ['name' => 'Kasir', 'permission_ids' => [$permission->id]]);

    $this->assertDatabaseHas('roles', ['tenant_id' => $tenantA->id, 'name' => 'Kasir', 'slug' => 'kasir']);
    $this->assertDatabaseHas('roles', ['tenant_id' => $tenantB->id, 'name' => 'Kasir', 'slug' => 'kasir']);
});

test('index lists system roles and the tenant custom roles with counts', function () {
    $tenant = Tenant::factory()->create();
    $admin = User::factory()->create(['tenant_id' => $tenant->id, 'role' => 'admin']);
    $permission = makeRolePermission('material.view.idx', 'material');

    // Create a system role (withoutGlobalScope because this test is not acting as any user yet)
    DB::table('roles')->insert([
        'tenant_id' => null,
        'name' => 'Manager',
        'slug' => 'manager-idx',
        'is_system_role' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Create a custom role directly (tenant-scoped)
    $customRole = Role::create([
        'tenant_id' => $tenant->id,
        'name' => 'Kasir',
        'slug' => 'kasir-idx',
        'is_system_role' => false,
    ]);
    $customRole->permissions()->sync([$permission->id]);

    $this->actingAs($admin);

    $response = $this->get(route('roles.index'));

    $response->assertInertia(fn ($page) => $page
        ->component('Roles/Index')
        ->has('systemRoles', 1)
        ->has('customRoles', 1)
        ->where('customRoles.0.name', 'Kasir')
        ->where('customRoles.0.permissions_count', 1)
    );
});

test('admin can edit own custom role', function () {
    $tenant = Tenant::factory()->create();
    $admin = User::factory()->create(['tenant_id' => $tenant->id, 'role' => 'admin']);
    $permission = makeRolePermission('material.view.edit', 'material');
    $role = Role::create(['tenant_id' => $tenant->id, 'name' => 'Kasir', 'slug' => 'kasir-edit', 'is_system_role' => false]);

    $this->actingAs($admin);

    $response = $this->put(route('roles.update', $role), [
        'name' => 'Kasir Senior',
        'description' => 'updated',
        'permission_ids' => [$permission->id],
    ]);

    $response->assertRedirect(route('roles.index'))->assertSessionHas('success');
    $this->assertDatabaseHas('roles', ['id' => $role->id, 'name' => 'Kasir Senior']);
    expect($role->fresh()->permissions()->pluck('permissions.id')->all())->toBe([$permission->id]);
});

test('editing a system role 404s', function () {
    $tenant = Tenant::factory()->create();
    $admin = User::factory()->create(['tenant_id' => $tenant->id, 'role' => 'admin']);
    $systemRole = Role::create(['tenant_id' => null, 'name' => 'Manager', 'slug' => 'manager-sys', 'is_system_role' => true]);

    $this->actingAs($admin);

    $this->get(route('roles.edit', $systemRole))->assertNotFound();
});

test('editing another tenant role 404s', function () {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();
    $adminA = User::factory()->create(['tenant_id' => $tenantA->id, 'role' => 'admin']);
    $roleB = Role::create(['tenant_id' => $tenantB->id, 'name' => 'Kasir B', 'slug' => 'kasir-b', 'is_system_role' => false]);

    $this->actingAs($adminA);

    $this->get(route('roles.edit', $roleB))->assertNotFound();
});

test('deleting an unused custom role succeeds', function () {
    $tenant = Tenant::factory()->create();
    $admin = User::factory()->create(['tenant_id' => $tenant->id, 'role' => 'admin']);
    $role = Role::create(['tenant_id' => $tenant->id, 'name' => 'Kasir', 'slug' => 'kasir-del', 'is_system_role' => false]);

    $this->actingAs($admin);

    $response = $this->delete(route('roles.destroy', $role));

    $response->assertRedirect(route('roles.index'))->assertSessionHas('success');
    $this->assertDatabaseMissing('roles', ['id' => $role->id]);
});

test('deleting a role assigned to staff is blocked', function () {
    $tenant = Tenant::factory()->create();
    $admin = User::factory()->create(['tenant_id' => $tenant->id, 'role' => 'admin']);
    $role = Role::create(['tenant_id' => $tenant->id, 'name' => 'Kasir', 'slug' => 'kasir-inuse', 'is_system_role' => false]);
    Staff::factory()->create(['tenant_id' => $tenant->id, 'role_id' => $role->id]);

    $this->actingAs($admin);

    $response = $this->delete(route('roles.destroy', $role));

    $response->assertRedirect()->assertSessionHas('error');
    $this->assertDatabaseHas('roles', ['id' => $role->id]);
});

test('deleting a system role 404s', function () {
    $tenant = Tenant::factory()->create();
    $admin = User::factory()->create(['tenant_id' => $tenant->id, 'role' => 'admin']);
    $systemRole = Role::create(['tenant_id' => null, 'name' => 'Manager', 'slug' => 'manager-del', 'is_system_role' => true]);

    $this->actingAs($admin);

    $this->delete(route('roles.destroy', $systemRole))->assertNotFound();
});
