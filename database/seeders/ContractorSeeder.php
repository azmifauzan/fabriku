<?php

namespace Database\Seeders;

use App\Models\Contractor;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class ContractorSeeder extends Seeder
{
    public function run(): void
    {
        // Get the demo tenant
        $demoTenant = Tenant::where('slug', 'demo')->first();

        if (! $demoTenant) {
            return;
        }

        // Create contractors for demo tenant
        $contractors = [
            [
                'name' => 'Bu Siti Penjahit',
                'contact_person' => 'Siti Aminah',
                'phone' => '081234567890',
                'email' => 'siti.penjahit@example.com',
                'address' => 'Jl. Merdeka No. 123, Jakarta Selatan',
                'type' => 'individual',
                'specialty' => 'sewing',
                'rate_per_piece' => 15000.00,
                'status' => 'active',
                'notes' => 'Penjahit berpengalaman 10 tahun, spesialis mukena dan gamis',
            ],
            [
                'name' => 'CV Karya Mandiri',
                'contact_person' => 'Budi Santoso',
                'phone' => '081987654321',
                'email' => 'info@karyamandiri.com',
                'address' => 'Jl. Industri Raya No. 45, Tangerang',
                'type' => 'company',
                'specialty' => 'sewing',
                'rate_per_piece' => 12000.00,
                'rate_per_hour' => 50000.00,
                'status' => 'active',
                'notes' => 'Perusahaan garment dengan kapasitas produksi besar',
            ],
            [
                'name' => 'Toko Kue Mama Sarah',
                'contact_person' => 'Sarah Wulandari',
                'phone' => '082345678901',
                'email' => 'mamasarah@gmail.com',
                'address' => 'Jl. Raya Bogor KM 15, Depok',
                'type' => 'individual',
                'specialty' => 'baking',
                'rate_per_piece' => 25000.00,
                'status' => 'active',
                'notes' => 'Spesialis kue tradisional dan modern, hygiene terjamin',
            ],
            [
                'name' => 'Pak Joko Tukang Kayu',
                'contact_person' => 'Joko Prasetyo',
                'phone' => '083456789012',
                'email' => null,
                'address' => 'Jl. Sukamaju No. 78, Bekasi',
                'type' => 'individual',
                'specialty' => 'crafting',
                'rate_per_hour' => 75000.00,
                'status' => 'active',
                'notes' => 'Tukang kayu ahli untuk furniture custom',
            ],
            [
                'name' => 'Bu Indri (Non-Aktif)',
                'contact_person' => 'Indri Lestari',
                'phone' => '084567890123',
                'email' => 'indri@example.com',
                'address' => 'Jl. Melati No. 12, Jakarta Timur',
                'type' => 'individual',
                'specialty' => 'sewing',
                'rate_per_piece' => 18000.00,
                'status' => 'inactive',
                'notes' => 'Sedang cuti melahirkan',
            ],
        ];

        foreach ($contractors as $contractorData) {
            Contractor::create(array_merge($contractorData, [
                'tenant_id' => $demoTenant->id,
            ]));
        }

        // Create additional random contractors
        Contractor::factory()
            ->count(10)
            ->forTenant($demoTenant->id)
            ->create();

        // Create some with specific states
        Contractor::factory()
            ->count(3)
            ->forTenant($demoTenant->id)
            ->sewing()
            ->individual()
            ->create();

        Contractor::factory()
            ->count(2)
            ->forTenant($demoTenant->id)
            ->baking()
            ->company()
            ->create();

        Contractor::factory()
            ->count(2)
            ->forTenant($demoTenant->id)
            ->inactive()
            ->create();
    }
}
