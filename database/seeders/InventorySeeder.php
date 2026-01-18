<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\Pattern;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'demo-garment')->first();

        if (! $tenant) {
            $this->command->warn('Demo tenant not found. Run TenantSeeder first.');

            return;
        }

        $this->command->info('Seeding inventory locations...');

        // Create inventory locations (racks)
        $locations = [
            [
                'tenant_id' => $tenant->id,
                'name' => 'Rak A1 - Mukena Grade A',
                'capacity' => 100,
                'is_active' => true,
            ],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Rak A2 - Mukena Grade B',
                'capacity' => 100,
                'is_active' => true,
            ],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Rak B1 - Daster/Gamis',
                'capacity' => 150,
                'is_active' => true,
            ],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Rak C1 - Produk Reject',
                'capacity' => 50,
                'is_active' => true,
            ],
        ];

        foreach ($locations as $locationData) {
            InventoryLocation::create($locationData);
        }

        $this->command->info('Seeding inventory items...');

        // Get patterns for reference
        $mukenaBaliPattern = Pattern::where('tenant_id', $tenant->id)
            ->where('product_type', 'mukena')
            ->where('name', 'like', '%Bali%')
            ->first();

        $dasterPattern = Pattern::where('tenant_id', $tenant->id)
            ->where('product_type', 'daster')
            ->first();

        // Get locations by name instead of rack
        $rakA1 = InventoryLocation::where('tenant_id', $tenant->id)->where('name', 'like', '%A1%')->first();
        $rakA2 = InventoryLocation::where('tenant_id', $tenant->id)->where('name', 'like', '%A2%')->first();
        $rakB1 = InventoryLocation::where('tenant_id', $tenant->id)->where('name', 'like', '%B1%')->first();
        $rakC1 = InventoryLocation::where('tenant_id', $tenant->id)->where('name', 'like', '%C1%')->first();

        // Create inventory items
        $items = [];

        if ($mukenaBaliPattern) {
            // Mukena Bali Grade A - Various colors and sizes
            $colors = ['Putih', 'Cream', 'Abu-abu', 'Hitam', 'Navy'];
            $sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];

            foreach ($colors as $color) {
                foreach ($sizes as $size) {
                    $items[] = [
                        'tenant_id' => $tenant->id,
                        'pattern_id' => $mukenaBaliPattern->id,
                        'name' => "Mukena Bali {$size} - {$color}",
                        'attributes' => [
                            'color' => $color,
                            'size' => $size,
                            'material' => 'Katun Jepang',
                        ],
                        'category' => 'garment',
                        'current_stock' => fake()->numberBetween(5, 50),
                        'reserved_stock' => 0,
                        'minimum_stock' => 5,
                        'unit_cost' => 85000,
                        'selling_price' => 125000,
                        'inventory_location_id' => $rakA1->id,
                        'batch_number' => 'PB-2026-'.str_pad(fake()->numberBetween(1, 50), 4, '0', STR_PAD_LEFT),
                        'quality_grade' => 'A',
                        'status' => 'available',
                    ];
                }
            }

            // Add some Grade B mukena (slightly lower stock and price)
            $items[] = [
                'tenant_id' => $tenant->id,
                'pattern_id' => $mukenaBaliPattern->id,
                'name' => 'Mukena Bali L - Putih (Grade B)',
                'attributes' => [
                    'color' => 'Putih',
                    'size' => 'L',
                    'material' => 'Katun Jepang',
                    'defect' => 'Jahitan sedikit tidak rata',
                ],
                'category' => 'garment',
                'current_stock' => 8,
                'reserved_stock' => 0,
                'minimum_stock' => 3,
                'unit_cost' => 85000,
                'selling_price' => 100000,
                'inventory_location_id' => $rakA2->id,
                'batch_number' => 'PB-2026-0012',
                'quality_grade' => 'B',
                'status' => 'available',
            ];
        }

        if ($dasterPattern) {
            // Daster items
            $dasterColors = ['Merah', 'Biru', 'Hijau', 'Ungu'];
            foreach ($dasterColors as $color) {
                $items[] = [
                    'tenant_id' => $tenant->id,
                    'pattern_id' => $dasterPattern->id,
                    'name' => "Daster All Size - {$color}",
                    'attributes' => [
                        'color' => $color,
                        'size' => 'All Size',
                        'material' => 'Katun Rayon',
                    ],
                    'category' => 'garment',
                    'current_stock' => fake()->numberBetween(10, 40),
                    'reserved_stock' => 0,
                    'minimum_stock' => 5,
                    'unit_cost' => 45000,
                    'selling_price' => 75000,
                    'inventory_location_id' => $rakB1->id,
                    'batch_number' => 'PB-2026-'.str_pad(fake()->numberBetween(51, 80), 4, '0', STR_PAD_LEFT),
                    'quality_grade' => 'A',
                    'status' => 'available',
                ];
            }
        }

        // Add some low stock items (for testing alerts)
        $items[] = [
            'tenant_id' => $tenant->id,
            'pattern_id' => $mukenaBaliPattern?->id,
            'name' => 'Mukena Bali XL - Pink (Low Stock)',
            'attributes' => [
                'color' => 'Pink',
                'size' => 'XL',
                'material' => 'Katun Jepang',
            ],
            'category' => 'garment',
            'current_stock' => 2, // Below minimum
            'reserved_stock' => 0,
            'minimum_stock' => 5,
            'unit_cost' => 85000,
            'selling_price' => 125000,
            'inventory_location_id' => $rakA1->id,
            'batch_number' => 'PB-2026-0099',
            'quality_grade' => 'A',
            'status' => 'available',
        ];

        // Add reject items
        $items[] = [
            'tenant_id' => $tenant->id,
            'pattern_id' => $mukenaBaliPattern?->id,
            'name' => 'Mukena Bali M - Putih (Reject)',
            'attributes' => [
                'color' => 'Putih',
                'size' => 'M',
                'material' => 'Katun Jepang',
                'defect' => 'Jahitan rusak parah, noda besar',
            ],
            'category' => 'garment',
            'current_stock' => 5,
            'reserved_stock' => 0,
            'minimum_stock' => 0,
            'unit_cost' => 85000,
            'selling_price' => 50000,
            'inventory_location_id' => $rakC1->id,
            'batch_number' => 'PB-2026-0013',
            'quality_grade' => 'reject',
            'status' => 'available',
        ];

        // Bulk insert items
        foreach ($items as $itemData) {
            InventoryItem::create($itemData);
        }

        $this->command->info('✓ Inventory seeded successfully!');
        $this->command->info("  - {$locations->count()} locations created");
        $this->command->info('  - '.count($items).' inventory items created');
    }
}
