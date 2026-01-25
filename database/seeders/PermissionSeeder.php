<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Tenant Management
            ['name' => 'View Tenants', 'slug' => 'tenant.view', 'module' => 'tenant', 'description' => 'View tenant list'],
            ['name' => 'Create Tenant', 'slug' => 'tenant.create', 'module' => 'tenant', 'description' => 'Create new tenant'],
            ['name' => 'Edit Tenant', 'slug' => 'tenant.edit', 'module' => 'tenant', 'description' => 'Edit tenant information'],
            ['name' => 'Delete Tenant', 'slug' => 'tenant.delete', 'module' => 'tenant', 'description' => 'Delete tenant'],
            ['name' => 'Suspend Tenant', 'slug' => 'tenant.suspend', 'module' => 'tenant', 'description' => 'Suspend/activate tenant'],

            // User Management
            ['name' => 'View Users', 'slug' => 'user.view', 'module' => 'user', 'description' => 'View user list'],
            ['name' => 'Create User', 'slug' => 'user.create', 'module' => 'user', 'description' => 'Create new user'],
            ['name' => 'Edit User', 'slug' => 'user.edit', 'module' => 'user', 'description' => 'Edit user information'],
            ['name' => 'Delete User', 'slug' => 'user.delete', 'module' => 'user', 'description' => 'Delete user'],
            ['name' => 'Reset Password', 'slug' => 'user.reset_password', 'module' => 'user', 'description' => 'Reset user password'],

            // Role Management
            ['name' => 'View Roles', 'slug' => 'role.view', 'module' => 'role', 'description' => 'View roles'],
            ['name' => 'Create Role', 'slug' => 'role.create', 'module' => 'role', 'description' => 'Create custom role'],
            ['name' => 'Edit Role', 'slug' => 'role.edit', 'module' => 'role', 'description' => 'Edit role permissions'],
            ['name' => 'Delete Role', 'slug' => 'role.delete', 'module' => 'role', 'description' => 'Delete role'],
            ['name' => 'Assign Role', 'slug' => 'role.assign', 'module' => 'role', 'description' => 'Assign role to user'],

            // Material Management
            ['name' => 'View Materials', 'slug' => 'material.view', 'module' => 'material', 'description' => 'View materials'],
            ['name' => 'Create Material', 'slug' => 'material.create', 'module' => 'material', 'description' => 'Create material'],
            ['name' => 'Edit Material', 'slug' => 'material.edit', 'module' => 'material', 'description' => 'Edit material'],
            ['name' => 'Delete Material', 'slug' => 'material.delete', 'module' => 'material', 'description' => 'Delete material'],

            // Pattern Management
            ['name' => 'View Patterns', 'slug' => 'pattern.view', 'module' => 'pattern', 'description' => 'View patterns'],
            ['name' => 'Create Pattern', 'slug' => 'pattern.create', 'module' => 'pattern', 'description' => 'Create pattern'],
            ['name' => 'Edit Pattern', 'slug' => 'pattern.edit', 'module' => 'pattern', 'description' => 'Edit pattern'],
            ['name' => 'Delete Pattern', 'slug' => 'pattern.delete', 'module' => 'pattern', 'description' => 'Delete pattern'],

            // Preparation Management
            ['name' => 'View Preparations', 'slug' => 'preparation.view', 'module' => 'preparation', 'description' => 'View preparation orders'],
            ['name' => 'Create Preparation', 'slug' => 'preparation.create', 'module' => 'preparation', 'description' => 'Create preparation order'],
            ['name' => 'Edit Preparation', 'slug' => 'preparation.edit', 'module' => 'preparation', 'description' => 'Edit preparation order'],
            ['name' => 'Delete Preparation', 'slug' => 'preparation.delete', 'module' => 'preparation', 'description' => 'Delete preparation order'],

            // Production Management
            ['name' => 'View Production', 'slug' => 'production.view', 'module' => 'production', 'description' => 'View production orders'],
            ['name' => 'Create Production', 'slug' => 'production.create', 'module' => 'production', 'description' => 'Create production order'],
            ['name' => 'Edit Production', 'slug' => 'production.edit', 'module' => 'production', 'description' => 'Edit production order'],
            ['name' => 'Delete Production', 'slug' => 'production.delete', 'module' => 'production', 'description' => 'Delete production order'],

            // Inventory Management
            ['name' => 'View Inventory', 'slug' => 'inventory.view', 'module' => 'inventory', 'description' => 'View inventory items'],
            ['name' => 'Create Inventory', 'slug' => 'inventory.create', 'module' => 'inventory', 'description' => 'Create inventory item'],
            ['name' => 'Edit Inventory', 'slug' => 'inventory.edit', 'module' => 'inventory', 'description' => 'Edit inventory item'],
            ['name' => 'Delete Inventory', 'slug' => 'inventory.delete', 'module' => 'inventory', 'description' => 'Delete inventory item'],

            // Sales Management
            ['name' => 'View Sales', 'slug' => 'sales.view', 'module' => 'sales', 'description' => 'View sales orders'],
            ['name' => 'Create Sales', 'slug' => 'sales.create', 'module' => 'sales', 'description' => 'Create sales order'],
            ['name' => 'Edit Sales', 'slug' => 'sales.edit', 'module' => 'sales', 'description' => 'Edit sales order'],
            ['name' => 'Delete Sales', 'slug' => 'sales.delete', 'module' => 'sales', 'description' => 'Delete sales order'],

            // Reports
            ['name' => 'View Reports', 'slug' => 'report.view', 'module' => 'report', 'description' => 'View reports'],
            ['name' => 'Export Reports', 'slug' => 'report.export', 'module' => 'report', 'description' => 'Export reports'],

            // Settings
            ['name' => 'View Settings', 'slug' => 'settings.view', 'module' => 'settings', 'description' => 'View settings'],
            ['name' => 'Edit Settings', 'slug' => 'settings.edit', 'module' => 'settings', 'description' => 'Edit settings'],

            // Audit Logs
            ['name' => 'View Audit Logs', 'slug' => 'audit.view', 'module' => 'audit', 'description' => 'View audit logs'],
            ['name' => 'Export Audit Logs', 'slug' => 'audit.export', 'module' => 'audit', 'description' => 'Export audit logs'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }
    }
}
