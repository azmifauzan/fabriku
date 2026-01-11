<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create demo tenant - GARMENT CATEGORY
        $garmentTenant = Tenant::create([
            'name' => 'Konveksi Fabriku',
            'slug' => 'konveksi-demo',
            'email' => 'konveksi@fabriku.com',
            'phone' => '081234567890',
            'address' => 'Jl. Raya Garment No. 123, Jakarta',
            'is_active' => true,
            'business_category' => 'garment',
            'category_settings' => [
                'default_waste_percentage' => 5.0,
                'default_quality_grade' => 'Grade A',
            ],
            'subscription_plan' => 'professional',
            'subscription_expires_at' => now()->addYear(),
            'settings' => [
                'currency' => 'IDR',
                'timezone' => 'Asia/Jakarta',
            ],
        ]);

        // Create admin user for garment tenant
        User::create([
            'tenant_id' => $garmentTenant->id,
            'name' => 'Admin Konveksi',
            'email' => 'admin@konveksi.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '081234567890',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create manager user
        User::create([
            'tenant_id' => $garmentTenant->id,
            'name' => 'Manager Produksi',
            'email' => 'manager@konveksi.com',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'phone' => '081234567891',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create staff users
        User::create([
            'tenant_id' => $garmentTenant->id,
            'name' => 'Staff Cutting',
            'email' => 'cutting@konveksi.com',
            'password' => Hash::make('password'),
            'role' => 'production_staff',
            'phone' => '081234567892',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        User::create([
            'tenant_id' => $garmentTenant->id,
            'name' => 'Staff Gudang',
            'email' => 'gudang@konveksi.com',
            'password' => Hash::make('password'),
            'role' => 'warehouse_staff',
            'phone' => '081234567893',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        User::create([
            'tenant_id' => $garmentTenant->id,
            'name' => 'Staff Penjualan',
            'email' => 'sales@konveksi.com',
            'password' => Hash::make('password'),
            'role' => 'sales_staff',
            'phone' => '081234567894',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create demo tenant - FOOD CATEGORY
        $foodTenant = Tenant::create([
            'name' => 'Kue Mama Homemade',
            'slug' => 'kue-mama',
            'email' => 'kuemama@fabriku.com',
            'phone' => '082234567890',
            'address' => 'Jl. Kue Basah No. 88, Bandung',
            'is_active' => true,
            'business_category' => 'food',
            'category_settings' => [
                'default_shelf_life_days' => 7,
                'expired_alert_days' => 3,
            ],
            'subscription_plan' => 'starter',
            'subscription_expires_at' => now()->addMonths(6),
            'settings' => [
                'currency' => 'IDR',
                'timezone' => 'Asia/Jakarta',
            ],
        ]);

        // Create admin user for food tenant
        User::create([
            'tenant_id' => $foodTenant->id,
            'name' => 'Ibu Mama',
            'email' => 'admin@kuemama.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '082234567890',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create staff for food tenant
        User::create([
            'tenant_id' => $foodTenant->id,
            'name' => 'Staff Dapur',
            'email' => 'dapur@kuemama.com',
            'password' => Hash::make('password'),
            'role' => 'production_staff',
            'phone' => '082234567891',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        User::create([
            'tenant_id' => $foodTenant->id,
            'name' => 'Staff Sales',
            'email' => 'sales@kuemama.com',
            'password' => Hash::make('password'),
            'role' => 'sales_staff',
            'phone' => '082234567892',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }
}
