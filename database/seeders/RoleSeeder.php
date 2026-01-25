<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define roles with their permissions
        $roles = [
            [
                'name' => 'Tenant Admin',
                'slug' => 'tenant_admin',
                'description' => 'Full access within tenant',
                'is_system_role' => true,
                'permissions' => [
                    'user.view', 'user.create', 'user.edit', 'user.delete', 'user.reset_password',
                    'role.view', 'role.assign',
                    'material.view', 'material.create', 'material.edit', 'material.delete',
                    'pattern.view', 'pattern.create', 'pattern.edit', 'pattern.delete',
                    'preparation.view', 'preparation.create', 'preparation.edit', 'preparation.delete',
                    'production.view', 'production.create', 'production.edit', 'production.delete',
                    'inventory.view', 'inventory.create', 'inventory.edit', 'inventory.delete',
                    'sales.view', 'sales.create', 'sales.edit', 'sales.delete',
                    'report.view', 'report.export',
                    'settings.view', 'settings.edit',
                    'audit.view', 'audit.export',
                ],
            ],
            [
                'name' => 'Manager',
                'slug' => 'manager',
                'description' => 'Access to all operational modules',
                'is_system_role' => true,
                'permissions' => [
                    'material.view', 'material.create', 'material.edit',
                    'pattern.view', 'pattern.create', 'pattern.edit',
                    'preparation.view', 'preparation.create', 'preparation.edit',
                    'production.view', 'production.create', 'production.edit',
                    'inventory.view', 'inventory.create', 'inventory.edit',
                    'sales.view', 'sales.create', 'sales.edit',
                    'report.view', 'report.export',
                ],
            ],
            [
                'name' => 'Production Staff',
                'slug' => 'production_staff',
                'description' => 'Access to materials, preparation, and production',
                'is_system_role' => true,
                'permissions' => [
                    'material.view',
                    'pattern.view',
                    'preparation.view', 'preparation.create', 'preparation.edit',
                    'production.view', 'production.create', 'production.edit',
                    'inventory.view',
                ],
            ],
            [
                'name' => 'Warehouse Staff',
                'slug' => 'warehouse_staff',
                'description' => 'Access to inventory and materials',
                'is_system_role' => true,
                'permissions' => [
                    'material.view', 'material.create', 'material.edit',
                    'inventory.view', 'inventory.create', 'inventory.edit',
                    'production.view',
                ],
            ],
            [
                'name' => 'Sales Staff',
                'slug' => 'sales_staff',
                'description' => 'Access to sales and customers',
                'is_system_role' => true,
                'permissions' => [
                    'inventory.view',
                    'sales.view', 'sales.create', 'sales.edit',
                ],
            ],
            [
                'name' => 'Viewer',
                'slug' => 'viewer',
                'description' => 'Read-only access to all modules',
                'is_system_role' => true,
                'permissions' => [
                    'material.view',
                    'pattern.view',
                    'preparation.view',
                    'production.view',
                    'inventory.view',
                    'sales.view',
                    'report.view',
                ],
            ],
        ];

        foreach ($roles as $roleData) {
            $permissionSlugs = $roleData['permissions'];
            unset($roleData['permissions']);

            // Create role (tenant_id is null for global roles)
            $role = Role::firstOrCreate(
                ['slug' => $roleData['slug'], 'tenant_id' => null],
                $roleData
            );

            // Attach permissions
            $permissions = Permission::whereIn('slug', $permissionSlugs)->get();
            $role->permissions()->sync($permissions);
        }
    }
}
