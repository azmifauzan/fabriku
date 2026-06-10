<?php

namespace Database\Seeders;

use App\Models\MaterialType;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class MaterialTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(?Tenant $tenant = null): void
    {
        if (! $tenant) {
            $tenant = Tenant::where('slug', 'demo')->first();
        }

        if (! $tenant) {
            $this->command->warn('Demo tenant not found. Run TenantSeeder first.');

            return;
        }

        $types = [
            // Garment Category Types
            [
                'tenant_id' => $tenant->id,
                'code' => 'kain',
                'name' => 'Kain',
                'unit' => 'meter',
                'description' => 'Kain utama untuk produksi garment (katun, rayon, wolfis, dll)',
                'sort_order' => 1,
            ],
            [
                'tenant_id' => $tenant->id,
                'code' => 'benang',
                'name' => 'Benang',
                'unit' => 'cone',
                'description' => 'Benang jahit dan bordir',
                'sort_order' => 2,
            ],
            [
                'tenant_id' => $tenant->id,
                'code' => 'aksesoris',
                'name' => 'Aksesories',
                'unit' => 'pcs',
                'description' => 'Kancing, resleting, pita, label, dll',
                'sort_order' => 3,
            ],
            [
                'tenant_id' => $tenant->id,
                'code' => 'kemasan',
                'name' => 'Kemasan',
                'unit' => 'pcs',
                'description' => 'Plastik, box, paper bag, dll',
                'sort_order' => 4,
            ],
            [
                'tenant_id' => $tenant->id,
                'code' => 'lainnya',
                'name' => 'Lainnya',
                'unit' => 'pcs',
                'description' => 'Material lain yang tidak termasuk kategori di atas',
                'sort_order' => 5,
            ],
        ];

        foreach ($types as $type) {
            MaterialType::updateOrCreate(
                ['tenant_id' => $type['tenant_id'], 'code' => $type['code']],
                $type
            );
        }
    }
}
