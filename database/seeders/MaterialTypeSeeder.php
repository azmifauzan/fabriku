<?php

namespace Database\Seeders;

use App\Models\MaterialType;
use Illuminate\Database\Seeder;

class MaterialTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'demo')->first();

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
                'description' => 'Kain utama untuk produksi garment (katun, rayon, wolfis, dll)',
                'sort_order' => 1,
            ],
            [
                'tenant_id' => $tenant->id,
                'code' => 'benang',
                'name' => 'Benang',
                'description' => 'Benang jahit dan bordir',
                'sort_order' => 2,
            ],
            [
                'tenant_id' => $tenant->id,
                'code' => 'aksesoris',
                'name' => 'Aksesories',
                'description' => 'Kancing, resleting, pita, label, dll',
                'sort_order' => 3,
            ],
            [
                'tenant_id' => $tenant->id,
                'code' => 'kemasan',
                'name' => 'Kemasan',
                'description' => 'Plastik, box, paper bag, dll',
                'sort_order' => 4,
            ],
            [
                'tenant_id' => $tenant->id,
                'code' => 'lainnya',
                'name' => 'Lainnya',
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
