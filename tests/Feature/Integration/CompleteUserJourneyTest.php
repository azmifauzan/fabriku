<?php

use App\Models\Contractor;
use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\Material;
use App\Models\MaterialReceipt;
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
            'business_category' => 'GARMENT',
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

    it('completes full garment workflow from registration to sale', function () {
        // Step 1: Login
        $this->actingAs($this->user)
            ->get(route('dashboard'))
            ->assertSuccessful()
            ->assertSee('Dashboard');

        // Step 2: Create Material Type
        $materialType = MaterialType::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Kain Katun',
            'category' => 'FABRIC',
        ]);

        // Step 3: Create Material
        $material = Material::factory()->create([
            'tenant_id' => $this->tenant->id,
            'material_type_id' => $materialType->id,
            'name' => 'Kain Katun Putih',
            'code' => 'KT-001',
            'unit' => 'METER',
            'stock_quantity' => 0,
        ]);

        // Step 4: Receive Material
        $materialReceipt = MaterialReceipt::factory()->create([
            'tenant_id' => $this->tenant->id,
            'material_id' => $material->id,
            'quantity' => 100,
            'unit_price' => 50000,
            'total_price' => 5000000,
            'received_at' => now(),
            'batch_number' => 'BATCH-001',
            'attributes' => [
                'color' => 'Putih',
                'width' => 150,
            ],
        ]);

        // Verify material stock updated
        $material->refresh();
        expect($material->stock_quantity)->toBe(100.0);

        // Step 5: Create Pattern
        $pattern = Pattern::factory()->create([
            'tenant_id' => $this->tenant->id,
            'code' => 'MKN-001',
            'name' => 'Mukena Dewasa',
            'product_type' => 'MUKENA',
            'category' => 'GARMENT',
        ]);

        // Step 6: Create Preparation Order (Cutting)
        $preparationOrder = PreparationOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'pattern_id' => $pattern->id,
            'order_number' => 'PREP-001',
            'planned_quantity' => 10,
            'actual_output' => null,
            'status' => 'DRAFT',
            'prepared_by' => $this->user->id,
        ]);

        // Add material usage
        $preparationOrder->materialUsages()->create([
            'tenant_id' => $this->tenant->id,
            'material_id' => $material->id,
            'quantity_used' => 20,
            'unit' => 'METER',
            'batch_number' => 'BATCH-001',
        ]);

        // Step 7: Start Preparation
        $this->actingAs($this->user)
            ->post(route('preparation-orders.update', $preparationOrder), [
                'status' => 'IN_PROGRESS',
                'pattern_id' => $pattern->id,
                'planned_quantity' => 10,
                'notes' => 'Mulai cutting',
            ])
            ->assertRedirect();

        // Step 8: Complete Preparation
        $this->actingAs($this->user)
            ->post(route('preparation-orders.update', $preparationOrder), [
                'status' => 'COMPLETED',
                'pattern_id' => $pattern->id,
                'planned_quantity' => 10,
                'actual_output' => 10,
                'notes' => 'Cutting selesai',
            ])
            ->assertRedirect();

        // Verify material stock deducted
        $material->refresh();
        expect($material->stock_quantity)->toBe(80.0);

        // Step 9: Create Contractor
        $contractor = Contractor::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Penjahit Bu Siti',
            'type' => 'SEWER',
            'phone' => '081234567890',
        ]);

        // Step 10: Create Production Order
        $productionOrder = ProductionOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'preparation_order_id' => $preparationOrder->id,
            'contractor_id' => $contractor->id,
            'order_number' => 'PROD-001',
            'quantity' => 10,
            'status' => 'PENDING',
            'production_type' => 'OUTSOURCE',
        ]);

        // Step 11: Send Production Order
        $this->actingAs($this->user)
            ->post(route('production-orders.send', $productionOrder))
            ->assertRedirect();

        $productionOrder->refresh();
        expect($productionOrder->status)->toBe('IN_PROGRESS');

        // Step 12: Complete Production and Add to Inventory
        $this->actingAs($this->user)
            ->post(route('production-orders.mark-complete', $productionOrder), [
                'completed_quantity' => 10,
                'quality_check' => [
                    'grade_a' => 8,
                    'grade_b' => 2,
                    'reject' => 0,
                ],
                'notes' => 'Jahit selesai',
            ])
            ->assertRedirect();

        $productionOrder->refresh();
        expect($productionOrder->status)->toBe('COMPLETED');

        // Step 13: Create Inventory Location
        $location = InventoryLocation::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Rak A1',
            'code' => 'RA1',
        ]);

        // Step 14: Add Items to Inventory
        $inventoryItems = [];
        for ($i = 1; $i <= 8; $i++) {
            $inventoryItems[] = InventoryItem::factory()->create([
                'tenant_id' => $this->tenant->id,
                'production_order_id' => $productionOrder->id,
                'inventory_location_id' => $location->id,
                'pattern_id' => $pattern->id,
                'sku' => "MKN-001-00{$i}",
                'product_name' => 'Mukena Dewasa',
                'product_type' => 'MUKENA',
                'initial_quantity' => 1,
                'current_quantity' => 1,
                'reserved_quantity' => 0,
                'status' => 'AVAILABLE',
                'quality_grade' => 'A',
            ]);
        }

        // Verify inventory created
        expect(count($inventoryItems))->toBe(8);

        // Step 15: Create Customer
        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Toko Busana Muslim',
            'type' => 'WHOLESALE',
            'phone' => '082345678901',
        ]);

        // Step 16: Create Sales Order
        $salesOrder = SalesOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'order_number' => 'SO-001',
            'order_date' => now(),
            'total_amount' => 2000000,
            'payment_status' => 'UNPAID',
            'order_status' => 'PENDING',
            'sales_channel' => 'OFFLINE',
        ]);

        // Add sales items
        $salesOrder->items()->create([
            'tenant_id' => $this->tenant->id,
            'inventory_item_id' => $inventoryItems[0]->id,
            'product_name' => 'Mukena Dewasa',
            'quantity' => 5,
            'unit_price' => 400000,
            'subtotal' => 2000000,
        ]);

        // Step 17: Confirm Sales Order (Should deduct stock)
        $this->actingAs($this->user)
            ->patch(route('sales-orders.update', $salesOrder), [
                'order_status' => 'CONFIRMED',
                'customer_id' => $customer->id,
                'order_date' => now()->format('Y-m-d'),
                'sales_channel' => 'OFFLINE',
                'discount_amount' => 0,
                'tax_amount' => 0,
                'shipping_cost' => 0,
                'notes' => 'Order confirmed',
                'items' => [
                    [
                        'inventory_item_id' => $inventoryItems[0]->id,
                        'quantity' => 5,
                        'unit_price' => 400000,
                    ],
                ],
            ])
            ->assertRedirect();

        // Step 18: Mark as Paid
        $this->actingAs($this->user)
            ->patch(route('sales-orders.update', $salesOrder), [
                'payment_status' => 'PAID',
                'customer_id' => $customer->id,
                'order_date' => now()->format('Y-m-d'),
                'order_status' => 'CONFIRMED',
                'sales_channel' => 'OFFLINE',
                'items' => [
                    [
                        'inventory_item_id' => $inventoryItems[0]->id,
                        'quantity' => 5,
                        'unit_price' => 400000,
                    ],
                ],
            ])
            ->assertRedirect();

        $salesOrder->refresh();
        expect($salesOrder->payment_status)->toBe('PAID');

        // Step 19: View Reports
        $this->actingAs($this->user)
            ->get(route('reports.material'))
            ->assertSuccessful();

        $this->actingAs($this->user)
            ->get(route('reports.inventory'))
            ->assertSuccessful();

        $this->actingAs($this->user)
            ->get(route('reports.production'))
            ->assertSuccessful();

        $this->actingAs($this->user)
            ->get(route('reports.sales'))
            ->assertSuccessful();

        // Step 20: Verify Dashboard Metrics
        $this->actingAs($this->user)
            ->get(route('dashboard'))
            ->assertSuccessful()
            ->assertSee('Dashboard');
    });
});

describe('Complete User Journey - Food Workflow', function () {
    beforeEach(function () {
        $this->tenant = Tenant::factory()->create([
            'business_category' => 'FOOD',
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

    it('completes full food workflow from registration to sale', function () {
        // Step 1: Login
        $this->actingAs($this->user)
            ->get(route('dashboard'))
            ->assertSuccessful();

        // Step 2: Create Material Type
        $materialType = MaterialType::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Tepung',
            'category' => 'BAKING',
        ]);

        // Step 3: Create Material (with expiry date)
        $material = Material::factory()->create([
            'tenant_id' => $this->tenant->id,
            'material_type_id' => $materialType->id,
            'name' => 'Tepung Terigu',
            'code' => 'TPG-001',
            'unit' => 'KG',
            'stock_quantity' => 0,
        ]);

        // Step 4: Receive Material
        $materialReceipt = MaterialReceipt::factory()->create([
            'tenant_id' => $this->tenant->id,
            'material_id' => $material->id,
            'quantity' => 50,
            'unit_price' => 15000,
            'total_price' => 750000,
            'received_at' => now(),
            'batch_number' => 'BATCH-TPG-001',
            'attributes' => [
                'expired_date' => now()->addMonths(6)->format('Y-m-d'),
                'storage_temp' => 'ROOM_TEMP',
            ],
        ]);

        $material->refresh();
        expect($material->stock_quantity)->toBe(50.0);

        // Step 5: Create Pattern (Recipe)
        $pattern = Pattern::factory()->create([
            'tenant_id' => $this->tenant->id,
            'code' => 'BWN-001',
            'name' => 'Brownies Coklat',
            'product_type' => 'BROWNIES',
            'category' => 'FOOD',
        ]);

        // Step 6: Create Preparation Order (Mixing)
        $preparationOrder = PreparationOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'pattern_id' => $pattern->id,
            'order_number' => 'PREP-FOOD-001',
            'planned_quantity' => 20,
            'actual_output' => null,
            'status' => 'DRAFT',
            'prepared_by' => $this->user->id,
        ]);

        // Add material usage
        $preparationOrder->materialUsages()->create([
            'tenant_id' => $this->tenant->id,
            'material_id' => $material->id,
            'quantity_used' => 5,
            'unit' => 'KG',
            'batch_number' => 'BATCH-TPG-001',
        ]);

        // Step 7: Complete Preparation
        $this->actingAs($this->user)
            ->patch(route('preparation-orders.update', $preparationOrder), [
                'status' => 'COMPLETED',
                'pattern_id' => $pattern->id,
                'planned_quantity' => 20,
                'actual_output' => 20,
                'notes' => 'Mixing selesai',
            ])
            ->assertRedirect();

        $material->refresh();
        expect($material->stock_quantity)->toBe(45.0);

        // Step 8: Create Production Order (Baking)
        $productionOrder = ProductionOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'preparation_order_id' => $preparationOrder->id,
            'order_number' => 'PROD-FOOD-001',
            'quantity' => 20,
            'status' => 'IN_PROGRESS',
            'production_type' => 'INTERNAL',
            'contractor_id' => null,
        ]);

        // Step 9: Complete Production
        $this->actingAs($this->user)
            ->post(route('production-orders.mark-complete', $productionOrder), [
                'completed_quantity' => 20,
                'quality_check' => [
                    'premium' => 18,
                    'standard' => 2,
                ],
                'notes' => 'Baking selesai',
            ])
            ->assertRedirect();

        $productionOrder->refresh();
        expect($productionOrder->status)->toBe('COMPLETED');

        // Step 10: Create Inventory Location
        $location = InventoryLocation::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Display Chiller',
            'code' => 'CH1',
        ]);

        // Step 11: Add to Inventory (with expiry date)
        $inventoryItem = InventoryItem::factory()->create([
            'tenant_id' => $this->tenant->id,
            'production_order_id' => $productionOrder->id,
            'inventory_location_id' => $location->id,
            'pattern_id' => $pattern->id,
            'sku' => 'BWN-001-001',
            'product_name' => 'Brownies Coklat',
            'product_type' => 'BROWNIES',
            'initial_quantity' => 20,
            'current_quantity' => 20,
            'reserved_quantity' => 0,
            'status' => 'AVAILABLE',
            'production_date' => now(),
            'expired_date' => now()->addDays(7),
        ]);

        // Step 12: Create Customer and Sales Order
        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Pelanggan Retail',
            'type' => 'RETAIL',
        ]);

        $salesOrder = SalesOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'order_number' => 'SO-FOOD-001',
            'order_date' => now(),
            'total_amount' => 500000,
            'payment_status' => 'PAID',
            'order_status' => 'CONFIRMED',
            'sales_channel' => 'ONLINE',
        ]);

        $salesOrder->items()->create([
            'tenant_id' => $this->tenant->id,
            'inventory_item_id' => $inventoryItem->id,
            'product_name' => 'Brownies Coklat',
            'quantity' => 10,
            'unit_price' => 50000,
            'subtotal' => 500000,
        ]);

        // Verify inventory stock deducted
        $inventoryItem->refresh();
        expect($inventoryItem->current_quantity)->toBe(10);

        // Step 13: Access Reports
        $this->actingAs($this->user)
            ->get(route('reports.inventory'))
            ->assertSuccessful()
            ->assertSee('Inventory Report');
    });
});
