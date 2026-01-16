<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenants = \App\Models\Tenant::all();

        foreach ($tenants as $tenant) {
            \App\Models\Staff::create([
                'tenant_id' => $tenant->id,
                'code' => 'STF001',
                'name' => 'Ahmad Supervisor',
                'position' => 'Supervisor Produksi',
                'phone' => '081234567890',
                'email' => 'ahmad.supervisor@example.com',
                'is_active' => true,
            ]);

            \App\Models\Staff::create([
                'tenant_id' => $tenant->id,
                'code' => 'STF002',
                'name' => 'Budi Manager',
                'position' => 'Manager Warehouse',
                'phone' => '081234567891',
                'email' => 'budi.manager@example.com',
                'is_active' => true,
            ]);

            \App\Models\Staff::create([
                'tenant_id' => $tenant->id,
                'code' => 'STF003',
                'name' => 'Siti Koordinator',
                'position' => 'Koordinator Penjahit',
                'phone' => '081234567892',
                'email' => 'siti.koordinator@example.com',
                'is_active' => true,
            ]);
        }
    }
}
