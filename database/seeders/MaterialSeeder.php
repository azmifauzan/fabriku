<?php

namespace Database\Seeders;

use App\Models\Material;
use App\Models\MaterialReceipt;
use App\Models\MaterialType;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class MaterialSeeder extends Seeder
{
    public function run(Tenant $tenant = null, User $user = null): void
    {
        if (! $tenant) {
            $tenant = Tenant::where('slug', 'demo')->first();
        }

        if (! $tenant) {
            $this->command->warn('Tenant not found for MaterialSeeder.');

            return;
        }

        // Use provided user or find first admin/user for the tenant
        if (! $user) {
            $user = User::where('tenant_id', $tenant->id)->first();
        }

        if (! $user) {
             // Fallback or skip if no user found (though unlikely if tenant exists)
             $this->command->warn('No user found for receipts.');
             return;
        }

        // Create materials
        $materials = [
            [
                'code' => 'KA-001',
                'name' => 'Kain Katun Rayon Premium',
                'type' => 'kain',
                'unit' => 'meter',
                'standard_price' => 45000,
                'reorder_point' => 50,
            ],
            [
                'code' => 'KA-002',
                'name' => 'Kain Wolfis Polos',
                'type' => 'kain',
                'unit' => 'meter',
                'standard_price' => 35000,
                'reorder_point' => 40,
            ],
            [
                'code' => 'KA-003',
                'name' => 'Kain Balotelli Motif',
                'type' => 'kain',
                'unit' => 'meter',
                'standard_price' => 50000,
                'reorder_point' => 30,
            ],
            [
                'code' => 'BN-001',
                'name' => 'Benang Jahit Polyester',
                'type' => 'benang',
                'unit' => 'pcs',
                'standard_price' => 15000,
                'reorder_point' => 100,
            ],
            [
                'code' => 'AK-001',
                'name' => 'Resleting 60cm',
                'type' => 'aksesoris',
                'unit' => 'pcs',
                'standard_price' => 5000,
                'reorder_point' => 200,
            ],
        ];

        foreach ($materials as $materialData) {
            // Find material type
            $typeCode = $materialData['type'] ?? 'lainnya';
            $materialType = MaterialType::where('tenant_id', $tenant->id)
                ->where('code', $typeCode)
                ->first();
            
            // If type not found, try to find by name or default
            if (!$materialType) {
                 // Try to seed or fallback
                 $materialType = MaterialType::firstOrCreate(
                    ['tenant_id' => $tenant->id, 'code' => $typeCode],
                    ['name' => ucfirst($typeCode), 'unit' => $materialData['unit'] ?? 'pcs']
                 );
            }

            // Map fields
            $createData = [
                'tenant_id' => $tenant->id,
                'is_active' => true,
                'material_type_id' => $materialType->id,
                'code' => $materialData['code'],
                'name' => $materialData['name'],
                'unit' => $materialData['unit'],
                'price_per_unit' => $materialData['standard_price'] ?? 0,
                'min_stock' => $materialData['reorder_point'] ?? 10,
                'stock_quantity' => 0, // Initial stock
            ];

            $material = Material::create($createData);

            // Create 2-3 receipts for each material
            $receiptCount = rand(2, 3);
            for ($i = 0; $i < $receiptCount; $i++) {
                $quantity = rand(50, 200);
                $unitPrice = ($material->price_per_unit) * (1 + (rand(-10, 10) / 100));

                MaterialReceipt::create([
                    'tenant_id' => $tenant->id,
                    'receipt_number' => 'RCP-'.date('Ymd', strtotime("-{$i} days")).'-'.rand(1000, 9999),
                    'material_id' => $material->id,
                    'supplier_name' => fake()->company(),
                    'receipt_date' => now()->subDays($i * 7),
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $quantity * $unitPrice,
                    'batch_number' => 'BATCH-'.rand(1000, 9999),
                    'received_by' => $user->id,
                ]);
            }
        }

        $this->command->info('Materials and receipts seeded successfully!');
    }
}
