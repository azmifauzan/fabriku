<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;

test('user with a system role has that role\'s permissions', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(['tenant_id' => $tenant->id, 'role' => 'staff']);

    $permission = Permission::create([
        'name' => 'material.view.sysrole',
        'slug' => 'material.view.sysrole',
        'module' => 'material',
        'description' => 'material.view.sysrole',
    ]);

    $systemRole = Role::create(['tenant_id' => null, 'name' => 'Manager', 'slug' => 'manager-perm', 'is_system_role' => true]);
    $systemRole->permissions()->sync([$permission->id]);

    $user->assignRole($systemRole);

    expect($user->hasRole('manager-perm'))->toBeTrue();
    expect($user->hasAnyRole(['manager-perm']))->toBeTrue();
    expect($user->hasPermission('material.view.sysrole'))->toBeTrue();
    expect($user->roles()->count())->toBe(1);
});
