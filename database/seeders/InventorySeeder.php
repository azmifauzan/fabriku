<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\ProductionOrder;
use App\Models\StockAdjustment;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(Tenant $tenant = null): void
    {
        if (!$tenant) {
             $tenant = Tenant::where('slug', 'demo-garment')->first();
        }

        if (! $tenant) {
            if ($this->command) {
                $this->command->warn('Tenant not found for InventorySeeder.');
            }
            return;
        }

        $user = User::where('tenant_id', $tenant->id)->first();

        if ($this->command) {
            $this->command->info('Seeding inventory locations...');
        }

        // Create inventory locations (racks)
        $locationData = [
            [
                'tenant_id' => $tenant->id,
                'code' => 'RAK-A1',
                'name' => 'Rak A1 - Mukena Grade A',
                'capacity' => 100,
                'is_active' => true,
            ],
            [
                'tenant_id' => $tenant->id,
                'code' => 'RAK-A2',
                'name' => 'Rak A2 - Mukena Grade B',
                'capacity' => 100,
                'is_active' => true,
            ],
            [
                'tenant_id' => $tenant->id,
                'code' => 'RAK-B1',
                'name' => 'Rak B1 - Daster/Gamis',
                'capacity' => 150,
                'is_active' => true,
            ],
            [
                'tenant_id' => $tenant->id,
                'code' => 'RAK-C1',
                'name' => 'Rak C1 - Produk Reject',
                'capacity' => 50,
                'is_active' => true,
            ],
        ];

        foreach ($locationData as $data) {
            InventoryLocation::create($data);
        }

        if ($this->command) {
            $this->command->info('Seeding inventory items...');
        }

        // Get locations
        $rakA1 = InventoryLocation::where('tenant_id', $tenant->id)->where('code', 'RAK-A1')->first();
        $rakA2 = InventoryLocation::where('tenant_id', $tenant->id)->where('code', 'RAK-A2')->first();
        $rakB1 = InventoryLocation::where('tenant_id', $tenant->id)->where('code', 'RAK-B1')->first();
        $rakC1 = InventoryLocation::where('tenant_id', $tenant->id)->where('code', 'RAK-C1')->first();

        // Get completed production orders
        $completedOrders = ProductionOrder::where('tenant_id', $tenant->id)
            ->where('status', 'completed')
            ->with('preparationOrder.pattern')
            ->get();

        $itemsCreated = 0;
        $adjustmentsCreated = 0;

        // Create inventory items from production orders
        foreach ($completedOrders as $order) {
            $pattern = $order->preparationOrder?->pattern;
            if (! $pattern) {
                continue;
            }

            $quantity = $order->preparationOrder->output_quantity ?? 50;
            $laborCost = $order->labor_cost ?? 0;
            $materialCost = 50000; // estimated

            $item = InventoryItem::create([
                'tenant_id' => $tenant->id,
                'sku' => 'INV-'.strtoupper(substr($pattern->product_type ?? 'item', 0, 4)).'-'.str_pad($itemsCreated + 1, 4, '0', STR_PAD_LEFT),
                'production_order_id' => $order->id,
                'source_type' => 'production',
                'location_id' => $rakA1->id,
                'product_name' => $pattern->name,
                'product_code' => $pattern->code,
                'target_quantity' => $quantity,
                'current_quantity' => $quantity,
                'reserved_quantity' => 0,
                'minimum_stock' => 5,
                'quality_grade' => 'A',
                'status' => 'available',
                'unit_cost' => ($materialCost + $laborCost) / max($quantity, 1),
                'selling_price' => (($materialCost + $laborCost) / max($quantity, 1)) * 1.5,
                'notes' => "Dari Production Order: {$order->order_number}",
            ]);

            $itemsCreated++;
        }

        // Create some manual entry items (opening balance)
        $openingBalanceItems = [
            [
                'tenant_id' => $tenant->id,
                'sku' => 'INV-OPEN-0001',
                'production_order_id' => null,
                'source_type' => 'opening_balance',
                'location_id' => $rakA1->id,
                'product_name' => 'Mukena Bali Premium - Putih',
                'product_code' => 'MKN-BL-001',
                'target_quantity' => 50,
                'current_quantity' => 45,
                'reserved_quantity' => 5,
                'minimum_stock' => 10,
                'quality_grade' => 'A',
                'status' => 'available',
                'unit_cost' => 85000,
                'selling_price' => 125000,
                'notes' => 'Stock awal migrasi dari sistem lama',
            ],
            [
                'tenant_id' => $tenant->id,
                'sku' => 'INV-OPEN-0002',
                'production_order_id' => null,
                'source_type' => 'opening_balance',
                'location_id' => $rakA2->id,
                'product_name' => 'Mukena Bali Premium - Cream (Grade B)',
                'product_code' => 'MKN-BL-002',
                'target_quantity' => 30,
                'current_quantity' => 30,
                'reserved_quantity' => 0,
                'minimum_stock' => 5,
                'quality_grade' => 'B',
                'status' => 'available',
                'unit_cost' => 85000,
                'selling_price' => 100000,
                'notes' => 'Stock awal - Grade B (jahitan tidak rata)',
            ],
            [
                'tenant_id' => $tenant->id,
                'sku' => 'INV-PURCH-0001',
                'production_order_id' => null,
                'source_type' => 'purchase',
                'location_id' => $rakB1->id,
                'product_name' => 'Daster Rayon Premium - Biru Navy',
                'product_code' => 'DST-RYN-001',
                'target_quantity' => 100,
                'current_quantity' => 100,
                'reserved_quantity' => 0,
                'minimum_stock' => 20,
                'quality_grade' => 'A',
                'status' => 'available',
                'unit_cost' => 45000,
                'selling_price' => 75000,
                'notes' => 'Pembelian langsung dari supplier',
            ],
            [
                'tenant_id' => $tenant->id,
                'sku' => 'INV-LOW-0001',
                'production_order_id' => null,
                'source_type' => 'opening_balance',
                'location_id' => $rakA1->id,
                'product_name' => 'Mukena Bali Premium - Pink (Low Stock)',
                'product_code' => 'MKN-BL-003',
                'target_quantity' => 20,
                'current_quantity' => 3, // Below minimum to trigger low stock alert
                'reserved_quantity' => 0,
                'minimum_stock' => 10,
                'quality_grade' => 'A',
                'status' => 'available',
                'unit_cost' => 85000,
                'selling_price' => 125000,
                'notes' => 'Stock awal - Low stock untuk testing alert',
            ],
            [
                'tenant_id' => $tenant->id,
                'sku' => 'INV-REJ-0001',
                'production_order_id' => null,
                'source_type' => 'opening_balance',
                'location_id' => $rakC1->id,
                'product_name' => 'Mukena Bali - Reject (Defect Besar)',
                'product_code' => 'MKN-BL-REJ',
                'target_quantity' => 10,
                'current_quantity' => 10,
                'reserved_quantity' => 0,
                'minimum_stock' => 0,
                'quality_grade' => 'Reject',
                'status' => 'damaged',
                'unit_cost' => 85000,
                'selling_price' => 40000,
                'notes' => 'Produk reject - jahitan rusak dan ada noda besar',
            ],
        ];

        foreach ($openingBalanceItems as $itemData) {
            $item = InventoryItem::create($itemData);
            $itemsCreated++;

            // Create opening balance adjustment record if source_type is opening_balance
            if ($itemData['source_type'] === 'opening_balance' && $user) {
                StockAdjustment::create([
                    'tenant_id' => $tenant->id,
                    'inventory_item_id' => $item->id,
                    'adjustment_type' => 'opening_balance',
                    'quantity_before' => 0,
                    'quantity_after' => $itemData['current_quantity'],
                    'adjustment_quantity' => $itemData['current_quantity'],
                    'reason' => 'Stock awal saat setup sistem',
                    'notes' => $itemData['notes'] ?? null,
                    'adjusted_by' => $user->id,
                ]);
                $adjustmentsCreated++;
            }
        }

        // Create some stock adjustment history for testing
        $existingItems = InventoryItem::where('tenant_id', $tenant->id)
            ->where('source_type', 'production')
            ->take(2)
            ->get();

        foreach ($existingItems as $item) {
            if (! $user) {
                continue;
            }

            // Correction adjustment
            $oldQty = $item->current_quantity;
            $newQty = $oldQty - 2;
            StockAdjustment::create([
                'tenant_id' => $tenant->id,
                'inventory_item_id' => $item->id,
                'adjustment_type' => 'correction',
                'quantity_before' => $oldQty,
                'quantity_after' => $newQty,
                'adjustment_quantity' => -2,
                'reason' => 'Koreksi setelah stock opname',
                'notes' => 'Ditemukan selisih 2 pcs saat pengecekan fisik',
                'adjusted_by' => $user->id,
            ]);
            $item->update(['current_quantity' => $newQty]);
            $adjustmentsCreated++;

            // Damage adjustment
            $oldQty = $item->current_quantity;
            $newQty = $oldQty - 1;
            StockAdjustment::create([
                'tenant_id' => $tenant->id,
                'inventory_item_id' => $item->id,
                'adjustment_type' => 'damage',
                'quantity_before' => $oldQty,
                'quantity_after' => $newQty,
                'adjustment_quantity' => -1,
                'reason' => 'Produk rusak',
                'notes' => 'Ditemukan 1 pcs dengan jahitan sobek',
                'adjusted_by' => $user->id,
            ]);
            $item->update(['current_quantity' => $newQty]);
            $adjustmentsCreated++;
        }

        if ($this->command) {
            $this->command->info('✓ Inventory seeded successfully!');
            $this->command->info('  - '.count($locationData).' locations created');
            $this->command->info("  - {$itemsCreated} inventory items created");
            $this->command->info("  - {$adjustmentsCreated} stock adjustments created");
        }
    }
}
