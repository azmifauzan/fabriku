<?php

use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\Pattern;
use App\Models\ProductionOrder;
use App\Models\SalesOrder;
use App\Models\StockAdjustment;
use App\Models\Tenant;
use App\Models\User;

describe('Inventory Management Integration', function () {
    beforeEach(function () {
        $this->tenant = Tenant::factory()->create([
            'business_category' => 'GARMENT',
        ]);

        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'email_verified_at' => now(),
        ]);
    });

    it('allows creating and managing inventory locations', function () {
        // Create location
        $response = $this->actingAs($this->user)
            ->post(route('inventory.locations.store'), [
                'name' => 'Rak A1',
                'code' => 'RA1',
                'description' => 'Rak untuk produk mukena',
            ]);

        $response->assertRedirect();

        $location = InventoryLocation::where('code', 'RA1')->first();
        expect($location)->not->toBeNull();
        expect($location->tenant_id)->toBe($this->tenant->id);

        // View locations
        $this->actingAs($this->user)
            ->get(route('inventory.locations.index'))
            ->assertSuccessful()
            ->assertSee('RA1');

        // Update location
        $this->actingAs($this->user)
            ->patch(route('inventory.locations.update', $location), [
                'name' => 'Rak A1 - Updated',
                'code' => 'RA1',
                'description' => 'Updated description',
            ])
            ->assertRedirect();

        $location->refresh();
        expect($location->name)->toBe('Rak A1 - Updated');
    });

    it('tracks inventory items with complete lifecycle', function () {
        $location = InventoryLocation::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $pattern = Pattern::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $productionOrder = ProductionOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'COMPLETED',
        ]);

        // Create inventory item
        $response = $this->actingAs($this->user)
            ->post(route('inventory.items.store'), [
                'production_order_id' => $productionOrder->id,
                'inventory_location_id' => $location->id,
                'pattern_id' => $pattern->id,
                'product_name' => 'Mukena Dewasa Putih',
                'product_type' => 'MUKENA',
                'sku' => 'MKN-001',
                'initial_quantity' => 10,
                'quality_grade' => 'A',
                'notes' => 'Produksi batch 1',
            ]);

        $response->assertRedirect();

        $item = InventoryItem::where('sku', 'MKN-001')->first();
        expect($item)->not->toBeNull();
        expect($item->current_quantity)->toBe(10);
        expect($item->status)->toBe('AVAILABLE');
    });

    it('handles stock adjustments correctly', function () {
        $item = InventoryItem::factory()->create([
            'tenant_id' => $this->tenant->id,
            'initial_quantity' => 100,
            'current_quantity' => 100,
            'reserved_quantity' => 0,
        ]);

        // Adjust stock (damage)
        $response = $this->actingAs($this->user)
            ->post(route('inventory.items.adjust', $item), [
                'adjustment_type' => 'DAMAGE',
                'quantity' => 5,
                'reason' => 'Produk cacat ditemukan',
                'notes' => 'Ditemukan saat quality check',
            ]);

        $response->assertRedirect();

        $item->refresh();
        expect($item->current_quantity)->toBe(95);

        // Verify adjustment recorded
        $adjustment = StockAdjustment::where('inventory_item_id', $item->id)->first();
        expect($adjustment)->not->toBeNull();
        expect($adjustment->adjustment_type)->toBe('DAMAGE');
        expect($adjustment->quantity)->toBe(-5);
    });

    it('tracks stock with different adjustment types', function () {
        $item = InventoryItem::factory()->create([
            'tenant_id' => $this->tenant->id,
            'current_quantity' => 50,
        ]);

        // Found items (positive adjustment)
        $this->actingAs($this->user)
            ->post(route('inventory.items.adjust', $item), [
                'adjustment_type' => 'FOUND',
                'quantity' => 3,
                'reason' => 'Ditemukan stok tersembunyi',
            ])
            ->assertRedirect();

        $item->refresh();
        expect($item->current_quantity)->toBe(53);

        // Lost items (negative adjustment)
        $this->actingAs($this->user)
            ->post(route('inventory.items.adjust', $item), [
                'adjustment_type' => 'LOST',
                'quantity' => 2,
                'reason' => 'Hilang/tidak ditemukan',
            ])
            ->assertRedirect();

        $item->refresh();
        expect($item->current_quantity)->toBe(51);
    });

    it('shows adjustment history', function () {
        $item = InventoryItem::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        // Create multiple adjustments
        StockAdjustment::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
            'inventory_item_id' => $item->id,
        ]);

        // View history
        $response = $this->actingAs($this->user)
            ->get(route('inventory.items.adjustments', $item));

        $response->assertSuccessful();
    });

    it('prevents negative stock', function () {
        $item = InventoryItem::factory()->create([
            'tenant_id' => $this->tenant->id,
            'current_quantity' => 5,
        ]);

        // Try to adjust more than available
        $response = $this->actingAs($this->user)
            ->post(route('inventory.items.adjust', $item), [
                'adjustment_type' => 'DAMAGE',
                'quantity' => 10, // More than current (5)
                'reason' => 'Test',
            ]);

        $response->assertSessionHasErrors();

        $item->refresh();
        expect($item->current_quantity)->toBe(5); // Should not change
    });

    it('tracks inventory for food category with expiry dates', function () {
        $this->tenant->update(['business_category' => 'FOOD']);

        $location = InventoryLocation::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $pattern = Pattern::factory()->create([
            'tenant_id' => $this->tenant->id,
            'category' => 'FOOD',
        ]);

        $productionOrder = ProductionOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        // Create food item with expiry
        $response = $this->actingAs($this->user)
            ->post(route('inventory.items.store'), [
                'production_order_id' => $productionOrder->id,
                'inventory_location_id' => $location->id,
                'pattern_id' => $pattern->id,
                'product_name' => 'Brownies Coklat',
                'product_type' => 'BROWNIES',
                'sku' => 'BWN-001',
                'initial_quantity' => 20,
                'production_date' => now()->format('Y-m-d'),
                'expired_date' => now()->addDays(7)->format('Y-m-d'),
                'notes' => 'Batch produksi pagi',
            ]);

        $response->assertRedirect();

        $item = InventoryItem::where('sku', 'BWN-001')->first();
        expect($item)->not->toBeNull();
        expect($item->expired_date)->not->toBeNull();
    });

    it('alerts on expiring food items', function () {
        $this->tenant->update(['business_category' => 'FOOD']);

        // Create item expiring soon
        $item = InventoryItem::factory()->create([
            'tenant_id' => $this->tenant->id,
            'product_name' => 'Cake Expiring Soon',
            'expired_date' => now()->addDays(2),
            'status' => 'AVAILABLE',
        ]);

        // Dashboard should show alert
        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertSuccessful();
        // Should see expiring items alert
    });
});

describe('QR Code Integration', function () {
    beforeEach(function () {
        $this->tenant = Tenant::factory()->create();

        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'email_verified_at' => now(),
            'role' => 'admin',
        ]);

        $this->item = InventoryItem::factory()->create([
            'tenant_id' => $this->tenant->id,
            'sku' => 'TEST-QR-001',
            'product_name' => 'Test Product',
        ]);
    });

    it('generates QR code for inventory item', function () {
        $response = $this->actingAs($this->user)
            ->get(route('inventory.items.qrcode.generate', $this->item));

        $response->assertSuccessful();
        $response->assertHeader('content-type', 'image/svg+xml');
    });

    it('looks up item by QR scan', function () {
        $response = $this->actingAs($this->user)
            ->post(route('inventory.items.scan-lookup'), [
                'qr_code' => $this->item->sku,
            ]);

        $response->assertSuccessful()
            ->assertJson([
                'success' => true,
                'redirect_url' => route('inventory.items.show', $this->item),
                'type' => 'item',
            ]);
    });

    it('handles invalid QR scan data', function () {
        $response = $this->actingAs($this->user)
            ->post(route('inventory.items.scan-lookup'), [
                'qr_code' => 'INVALID-SKU',
            ]);

        $response->assertStatus(404);
    });
});

describe('Inventory Visualization', function () {
    beforeEach(function () {
        $this->tenant = Tenant::factory()->create();

        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'email_verified_at' => now(),
        ]);
    });

    it('shows inventory visualization page', function () {
        $response = $this->actingAs($this->user)
            ->get(route('inventory.visualization'));

        $response->assertSuccessful();
    });

    it('displays items grouped by location', function () {
        // Create locations with items
        $location1 = InventoryLocation::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Rak A',
        ]);

        $location2 = InventoryLocation::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Rak B',
        ]);

        InventoryItem::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
            'inventory_location_id' => $location1->id,
        ]);

        InventoryItem::factory()->count(2)->create([
            'tenant_id' => $this->tenant->id,
            'inventory_location_id' => $location2->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('inventory.visualization'));

        $response->assertSuccessful()
            ->assertSee('Rak A')
            ->assertSee('Rak B');
    });
});

describe('Sales Order Stock Integration', function () {
    beforeEach(function () {
        $this->tenant = Tenant::factory()->create();

        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'email_verified_at' => now(),
        ]);

        $this->customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $this->item = InventoryItem::factory()->create([
            'tenant_id' => $this->tenant->id,
            'initial_quantity' => 100,
            'current_quantity' => 100,
            'reserved_quantity' => 0,
        ]);
    });

    it('reserves stock when sales order is created', function () {
        $response = $this->actingAs($this->user)
            ->post(route('sales-orders.store'), [
                'customer_id' => $this->customer->id,
                'order_date' => now()->format('Y-m-d'),
                'sales_channel' => 'OFFLINE',
                'order_status' => 'PENDING',
                'payment_status' => 'UNPAID',
                'items' => [
                    [
                        'inventory_item_id' => $this->item->id,
                        'quantity' => 10,
                        'unit_price' => 100000,
                    ],
                ],
            ]);

        $response->assertRedirect();

        $this->item->refresh();
        expect($this->item->reserved_quantity)->toBe(10);
        expect($this->item->current_quantity)->toBe(100);
    });

    it('deducts stock when order is confirmed', function () {
        $salesOrder = SalesOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'order_status' => 'PENDING',
        ]);

        $salesOrder->items()->create([
            'tenant_id' => $this->tenant->id,
            'inventory_item_id' => $this->item->id,
            'product_name' => $this->item->product_name,
            'quantity' => 15,
            'unit_price' => 100000,
            'subtotal' => 1500000,
        ]);

        // Reserve stock
        $this->item->update(['reserved_quantity' => 15]);

        // Confirm order
        $response = $this->actingAs($this->user)
            ->patch(route('sales-orders.update', $salesOrder), [
                'customer_id' => $this->customer->id,
                'order_date' => now()->format('Y-m-d'),
                'order_status' => 'CONFIRMED',
                'payment_status' => 'UNPAID',
                'sales_channel' => 'OFFLINE',
                'items' => [
                    [
                        'inventory_item_id' => $this->item->id,
                        'quantity' => 15,
                        'unit_price' => 100000,
                    ],
                ],
            ]);

        $response->assertRedirect();

        $this->item->refresh();
        expect($this->item->current_quantity)->toBe(85); // 100 - 15
        expect($this->item->reserved_quantity)->toBe(0);
    });

    it('releases reserved stock when order is cancelled', function () {
        $salesOrder = SalesOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'order_status' => 'PENDING',
        ]);

        $salesOrder->items()->create([
            'tenant_id' => $this->tenant->id,
            'inventory_item_id' => $this->item->id,
            'product_name' => $this->item->product_name,
            'quantity' => 20,
            'unit_price' => 100000,
            'subtotal' => 2000000,
        ]);

        $this->item->update(['reserved_quantity' => 20]);

        // Cancel order
        $response = $this->actingAs($this->user)
            ->patch(route('sales-orders.update', $salesOrder), [
                'customer_id' => $this->customer->id,
                'order_date' => now()->format('Y-m-d'),
                'order_status' => 'CANCELLED',
                'payment_status' => 'UNPAID',
                'sales_channel' => 'OFFLINE',
                'items' => [
                    [
                        'inventory_item_id' => $this->item->id,
                        'quantity' => 20,
                        'unit_price' => 100000,
                    ],
                ],
            ]);

        $response->assertRedirect();

        $this->item->refresh();
        expect($this->item->reserved_quantity)->toBe(0); // Should be released
        expect($this->item->current_quantity)->toBe(100); // Should not be deducted
    });

    it('prevents overselling inventory', function () {
        // Try to create order with more than available
        $response = $this->actingAs($this->user)
            ->post(route('sales-orders.store'), [
                'customer_id' => $this->customer->id,
                'order_date' => now()->format('Y-m-d'),
                'sales_channel' => 'OFFLINE',
                'items' => [
                    [
                        'inventory_item_id' => $this->item->id,
                        'quantity' => 150, // More than available (100)
                        'unit_price' => 100000,
                    ],
                ],
            ]);

        $response->assertSessionHasErrors();
    });
});
