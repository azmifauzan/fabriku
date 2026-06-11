<?php

use App\Models\Contractor;
use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\Material;
use App\Models\MaterialType;
use App\Models\Pattern;
use App\Models\PreparationOrder;
use App\Models\ProductionOrder;
use App\Models\SalesOrder;
use App\Models\Tenant;
use App\Models\User;

describe('Complete User Journey - Garment Workflow', function () {
    beforeEach(function () {
        $this->tenant = Tenant::factory()->create([
            'business_category' => 'garment',
            'is_active' => true,
            'subscription_plan' => 'PRO',
            'subscription_expires_at' => now()->addMonth(),
        ]);

        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    });

    it('completes full garment workflow from material receipt to sale', function () {
        // Step 1: Login & dashboard
        $this->actingAs($this->user)
            ->get(route('dashboard'))
            ->assertSuccessful();

        // Step 2-3: Master data material
        $materialType = MaterialType::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Kain Katun',
        ]);

        $material = Material::factory()->create([
            'tenant_id' => $this->tenant->id,
            'material_type_id' => $materialType->id,
            'name' => 'Kain Katun Putih',
            'code' => 'KT-001',
            'unit' => 'meter',
            'stock_quantity' => 0,
        ]);

        // Step 4: Receive material via HTTP
        $this->actingAs($this->user)
            ->post(route('material-receipts.store'), [
                'material_id' => $material->id,
                'supplier_name' => 'PT Kain Sejahtera',
                'quantity' => 100,
                'unit_price' => 50000,
                'receipt_date' => now()->format('Y-m-d'),
                'batch_number' => 'BATCH-001',
            ])
            ->assertRedirect();

        $material->refresh();
        expect((float) $material->stock_quantity)->toBe(100.0);

        // Step 5: Create pattern
        $pattern = Pattern::factory()->create([
            'tenant_id' => $this->tenant->id,
            'code' => 'MKN-001',
            'name' => 'Mukena Dewasa',
        ]);

        // Step 6-7: Preparation order (cutting) sampai completed → stok deduct
        $prepPayload = [
            'pattern_id' => $pattern->id,
            'order_date' => now()->toDateString(),
            'output_quantity' => 10,
            'output_unit' => 'pieces',
            'materials_used' => [
                [
                    'material_id' => $material->id,
                    'material_name' => $material->name,
                    'quantity' => 20,
                    'unit' => 'meter',
                ],
            ],
        ];

        $this->actingAs($this->user)
            ->post(route('preparation-orders.store'), $prepPayload + ['status' => 'draft'])
            ->assertRedirect();

        $preparationOrder = PreparationOrder::where('tenant_id', $this->tenant->id)->first();
        expect($preparationOrder)->not->toBeNull();

        $this->actingAs($this->user)
            ->patch(route('preparation-orders.update', $preparationOrder), $prepPayload + ['status' => 'completed'])
            ->assertRedirect();

        $material->refresh();
        expect((float) $material->stock_quantity)->toBe(80.0);

        // Step 8: Contractor + production order eksternal
        $contractor = Contractor::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Penjahit Bu Siti',
        ]);

        $productionOrder = ProductionOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'preparation_order_id' => $preparationOrder->id,
            'type' => 'external',
            'contractor_id' => $contractor->id,
            'status' => 'draft',
        ]);

        // Step 9: Send ke kontraktor → status sent
        $this->actingAs($this->user)
            ->post(route('production-orders.send', $productionOrder))
            ->assertRedirect();

        $productionOrder->refresh();
        expect($productionOrder->status)->toBe('sent');

        // Step 10: Mark complete
        $this->actingAs($this->user)
            ->post(route('production-orders.mark-complete', $productionOrder))
            ->assertRedirect();

        $productionOrder->refresh();
        expect($productionOrder->status)->toBe('completed');

        // Step 11: Masukkan hasil produksi ke inventory via HTTP
        $location = InventoryLocation::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Rak A1',
            'code' => 'RA1',
        ]);

        $this->actingAs($this->user)
            ->post(route('inventory.items.store'), [
                'production_order_id' => $productionOrder->id,
                'location_id' => $location->id,
                'name' => 'Mukena Dewasa',
                'sku' => 'MKN-001-A',
                'target_quantity' => 10,
                'stock_quantity' => 8,
                'unit_cost' => 60000,
                'selling_price' => 400000,
                'quality_grade' => 'grade_a',
                'status' => 'available',
            ])
            ->assertRedirect();

        $inventoryItem = InventoryItem::where('sku', 'MKN-001-A')->first();
        expect($inventoryItem)->not->toBeNull();
        expect($inventoryItem->current_quantity)->toBe(8);

        // Step 12: Customer + sales order draft → confirmed → completed
        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Toko Busana Muslim',
        ]);

        $orderPayload = fn (string $status) => [
            'customer_id' => $customer->id,
            'order_date' => now()->format('Y-m-d'),
            'channel' => 'offline',
            'status' => $status,
            'payment_method' => 'transfer',
            'items' => [
                [
                    'inventory_item_id' => $inventoryItem->id,
                    'quantity' => 5,
                    'unit_price' => 400000,
                ],
            ],
        ];

        $this->actingAs($this->user)
            ->post(route('sales-orders.store'), $orderPayload('draft'))
            ->assertRedirect();

        $salesOrder = SalesOrder::where('tenant_id', $this->tenant->id)->latest('id')->first();
        expect($salesOrder->status)->toBe('draft');

        // Confirm → observer reserve
        $this->actingAs($this->user)
            ->patch(route('sales-orders.update', $salesOrder), $orderPayload('confirmed'))
            ->assertRedirect();

        $inventoryItem->refresh();
        expect($inventoryItem->reserved_quantity)->toBe(5);

        // Complete via update-status → observer deduct
        $this->actingAs($this->user)
            ->patch(route('sales-orders.update-status', $salesOrder), [
                'status' => 'completed',
            ])
            ->assertRedirect();

        // Update payment separately
        $this->actingAs($this->user)
            ->patch(route('sales-orders.update-payment', $salesOrder), [
                'paid_amount' => 2000000,
            ])
            ->assertRedirect();

        $inventoryItem->refresh();
        expect($inventoryItem->current_quantity)->toBe(3);
        expect($inventoryItem->reserved_quantity)->toBe(0);

        $salesOrder->refresh();
        expect($salesOrder->status)->toBe('completed');
        expect($salesOrder->payment_status)->toBe('paid');

        // Step 13: Reports + dashboard
        foreach (['reports.material', 'reports.inventory', 'reports.production', 'reports.sales'] as $report) {
            $this->actingAs($this->user)
                ->get(route($report))
                ->assertSuccessful();
        }

        $this->actingAs($this->user)
            ->get(route('dashboard'))
            ->assertSuccessful();
    });
});

describe('Complete User Journey - Food Workflow', function () {
    beforeEach(function () {
        $this->tenant = Tenant::factory()->create([
            'business_category' => 'food',
            'is_active' => true,
            'subscription_plan' => 'PRO',
            'subscription_expires_at' => now()->addMonth(),
        ]);

        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    });

    it('completes full food workflow from ingredient to sale', function () {
        // Step 1: Bahan baku
        $materialType = MaterialType::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Tepung',
        ]);

        $material = Material::factory()->create([
            'tenant_id' => $this->tenant->id,
            'material_type_id' => $materialType->id,
            'name' => 'Tepung Terigu',
            'code' => 'TPG-001',
            'unit' => 'kg',
            'stock_quantity' => 0,
        ]);

        $this->actingAs($this->user)
            ->post(route('material-receipts.store'), [
                'material_id' => $material->id,
                'supplier_name' => 'Toko Bahan Kue',
                'quantity' => 50,
                'unit_price' => 15000,
                'receipt_date' => now()->format('Y-m-d'),
                'batch_number' => 'BATCH-TPG-001',
            ])
            ->assertRedirect();

        $material->refresh();
        expect((float) $material->stock_quantity)->toBe(50.0);

        // Step 2: Resep + preparation (mixing) → completed
        $pattern = Pattern::factory()->create([
            'tenant_id' => $this->tenant->id,
            'code' => 'BWN-001',
            'name' => 'Brownies Coklat',
        ]);

        $prepPayload = [
            'pattern_id' => $pattern->id,
            'order_date' => now()->toDateString(),
            'output_quantity' => 20,
            'output_unit' => 'pieces',
            'materials_used' => [
                [
                    'material_id' => $material->id,
                    'material_name' => $material->name,
                    'quantity' => 5,
                    'unit' => 'kg',
                ],
            ],
        ];

        $this->actingAs($this->user)
            ->post(route('preparation-orders.store'), $prepPayload + ['status' => 'draft'])
            ->assertRedirect();

        $preparationOrder = PreparationOrder::where('tenant_id', $this->tenant->id)->first();

        $this->actingAs($this->user)
            ->patch(route('preparation-orders.update', $preparationOrder), $prepPayload + ['status' => 'completed'])
            ->assertRedirect();

        $material->refresh();
        expect((float) $material->stock_quantity)->toBe(45.0);

        // Step 3: Produksi internal (baking) → in_progress via send → complete
        $productionOrder = ProductionOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'preparation_order_id' => $preparationOrder->id,
            'type' => 'internal',
            'contractor_id' => null,
            'status' => 'draft',
        ]);

        $this->actingAs($this->user)
            ->post(route('production-orders.send', $productionOrder))
            ->assertRedirect();

        $productionOrder->refresh();
        expect($productionOrder->status)->toBe('in_progress');

        $this->actingAs($this->user)
            ->post(route('production-orders.mark-complete', $productionOrder))
            ->assertRedirect();

        $productionOrder->refresh();
        expect($productionOrder->status)->toBe('completed');

        // Step 4: Inventory dengan expired date
        $location = InventoryLocation::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Display Chiller',
            'code' => 'CH1',
        ]);

        $this->actingAs($this->user)
            ->post(route('inventory.items.store'), [
                'production_order_id' => $productionOrder->id,
                'location_id' => $location->id,
                'name' => 'Brownies Coklat',
                'sku' => 'BWN-001-001',
                'target_quantity' => 20,
                'stock_quantity' => 20,
                'unit_cost' => 20000,
                'selling_price' => 50000,
                'status' => 'available',
                'quality_grade' => 'grade_a',
                'expired_date' => now()->addDays(7)->format('Y-m-d'),
            ])
            ->assertRedirect();

        $inventoryItem = InventoryItem::where('sku', 'BWN-001-001')->first();
        expect($inventoryItem)->not->toBeNull();
        expect($inventoryItem->expired_date)->not->toBeNull();

        // Step 5: Jual 10 pcs sampai completed → stok 10
        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Pelanggan Retail',
        ]);

        $orderPayload = fn (string $status) => [
            'customer_id' => $customer->id,
            'order_date' => now()->format('Y-m-d'),
            'channel' => 'online',
            'status' => $status,
            'payment_method' => 'transfer',
            'items' => [
                [
                    'inventory_item_id' => $inventoryItem->id,
                    'quantity' => 10,
                    'unit_price' => 50000,
                ],
            ],
        ];

        $this->actingAs($this->user)
            ->post(route('sales-orders.store'), $orderPayload('draft'))
            ->assertRedirect();

        $salesOrder = SalesOrder::where('tenant_id', $this->tenant->id)->latest('id')->first();

        $this->actingAs($this->user)
            ->patch(route('sales-orders.update', $salesOrder), $orderPayload('confirmed'))
            ->assertRedirect();

        // Complete via update-status → observer deduct
        $this->actingAs($this->user)
            ->patch(route('sales-orders.update-status', $salesOrder), [
                'status' => 'completed',
            ])
            ->assertRedirect();

        // Pay via update-payment
        $this->actingAs($this->user)
            ->patch(route('sales-orders.update-payment', $salesOrder), [
                'paid_amount' => 500000,
            ])
            ->assertRedirect();

        $inventoryItem->refresh();
        expect($inventoryItem->current_quantity)->toBe(10);

        // Step 6: Report inventory
        $this->actingAs($this->user)
            ->get(route('reports.inventory'))
            ->assertSuccessful();
    });
});
