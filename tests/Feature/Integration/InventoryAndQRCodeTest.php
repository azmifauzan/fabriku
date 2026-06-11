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
            'status' => 'completed',
        ]);

        // Create inventory item
        $response = $this->actingAs($this->user)
            ->post(route('inventory.items.store'), [
                'production_order_id' => $productionOrder->id,
                'location_id' => $location->id,
                'name' => 'Mukena Dewasa Putih',
                'sku' => 'MKN-001',
                'target_quantity' => 10,
                'stock_quantity' => 10,
                'unit_cost' => 50000,
                'selling_price' => 95000,
                'quality_grade' => 'grade_a',
                'status' => 'available',
            ]);

        $response->assertRedirect();

        $item = InventoryItem::where('sku', 'MKN-001')->first();
        expect($item)->not->toBeNull();
        expect($item->current_quantity)->toBe(10);
        expect($item->status)->toBe('available');
    });

    it('handles stock adjustments correctly', function () {
        $item = InventoryItem::factory()->create([
            'tenant_id' => $this->tenant->id,
            'current_quantity' => 100,
            'reserved_quantity' => 0,
        ]);

        // Adjust stock (damage)
        $response = $this->actingAs($this->user)
            ->post(route('inventory.items.adjust', $item), [
                'type' => 'subtract',
                'adjustment_type' => 'damage',
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
        expect($adjustment->adjustment_type)->toBe('damage');
        expect($adjustment->quantity_after)->toBe(95);
    });

    it('tracks stock with different adjustment types', function () {
        $item = InventoryItem::factory()->create([
            'tenant_id' => $this->tenant->id,
            'current_quantity' => 50,
        ]);

        // Found items (positive adjustment)
        $this->actingAs($this->user)
            ->post(route('inventory.items.adjust', $item), [
                'type' => 'add',
                'adjustment_type' => 'found',
                'quantity' => 3,
                'reason' => 'Ditemukan stok tersembunyi',
            ])
            ->assertRedirect();

        $item->refresh();
        expect($item->current_quantity)->toBe(53);

        // Lost items (negative adjustment)
        $this->actingAs($this->user)
            ->post(route('inventory.items.adjust', $item), [
                'type' => 'subtract',
                'adjustment_type' => 'loss',
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
        ]);

        $productionOrder = ProductionOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        // Create food item with expiry
        $response = $this->actingAs($this->user)
            ->post(route('inventory.items.store'), [
                'production_order_id' => $productionOrder->id,
                'location_id' => $location->id,
                'name' => 'Brownies Coklat',
                'sku' => 'BWN-001',
                'target_quantity' => 20,
                'stock_quantity' => 20,
                'unit_cost' => 15000,
                'selling_price' => 30000,
                'status' => 'available',
                'quality_grade' => 'grade_a',
                'expired_date' => now()->addDays(7)->format('Y-m-d'),
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
            'current_quantity' => 100,
            'reserved_quantity' => 0,
            'status' => 'available',
        ]);
    });

    function makeOrderPayload($customer, $item, int $qty, string $status = 'draft'): array
    {
        return [
            'customer_id' => $customer->id,
            'order_date' => now()->format('Y-m-d'),
            'channel' => 'offline',
            'status' => $status,
            'payment_method' => 'cash',
            'items' => [
                [
                    'inventory_item_id' => $item->id,
                    'quantity' => $qty,
                    'unit_price' => 100000,
                ],
            ],
        ];
    }

    it('does not reserve stock while order is draft, reserves on confirm', function () {
        $this->actingAs($this->user)
            ->post(route('sales-orders.store'), makeOrderPayload($this->customer, $this->item, 10))
            ->assertRedirect();

        // Order dibuat sebagai draft — belum ada reservasi (observer semantics)
        $this->item->refresh();
        expect($this->item->reserved_quantity)->toBe(0);
        expect($this->item->current_quantity)->toBe(100);

        $order = SalesOrder::where('tenant_id', $this->tenant->id)->latest('id')->first();
        expect($order->status)->toBe('draft');

        // Confirm → observer reserve
        $this->actingAs($this->user)
            ->patch(route('sales-orders.update', $order), makeOrderPayload($this->customer, $this->item, 10, 'confirmed'))
            ->assertRedirect();

        $this->item->refresh();
        expect($this->item->reserved_quantity)->toBe(10);
        expect($this->item->current_quantity)->toBe(100);
    });

    it('deducts stock when order is completed', function () {
        $this->actingAs($this->user)
            ->post(route('sales-orders.store'), makeOrderPayload($this->customer, $this->item, 15))
            ->assertRedirect();

        $order = SalesOrder::where('tenant_id', $this->tenant->id)->latest('id')->first();

        $this->actingAs($this->user)
            ->patch(route('sales-orders.update', $order), makeOrderPayload($this->customer, $this->item, 15, 'confirmed'))
            ->assertRedirect();

        // Use update-status to complete
        $this->actingAs($this->user)
            ->patch(route('sales-orders.update-status', $order), ['status' => 'completed'])
            ->assertRedirect();

        $this->item->refresh();
        expect($this->item->current_quantity)->toBe(85);
        expect($this->item->reserved_quantity)->toBe(0);
    });

    it('releases reserved stock when order is cancelled', function () {
        $this->actingAs($this->user)
            ->post(route('sales-orders.store'), makeOrderPayload($this->customer, $this->item, 20))
            ->assertRedirect();

        $order = SalesOrder::where('tenant_id', $this->tenant->id)->latest('id')->first();

        $this->actingAs($this->user)
            ->patch(route('sales-orders.update', $order), makeOrderPayload($this->customer, $this->item, 20, 'confirmed'))
            ->assertRedirect();

        $this->item->refresh();
        expect($this->item->reserved_quantity)->toBe(20);

        // Use update-status to cancel
        $this->actingAs($this->user)
            ->patch(route('sales-orders.update-status', $order), ['status' => 'cancelled'])
            ->assertRedirect();

        $this->item->refresh();
        expect($this->item->reserved_quantity)->toBe(0);
        expect($this->item->current_quantity)->toBe(100);
    });

    it('prevents overselling inventory', function () {
        $this->actingAs($this->user)
            ->post(route('sales-orders.store'), makeOrderPayload($this->customer, $this->item, 150))
            ->assertSessionHasErrors();

        expect(SalesOrder::where('tenant_id', $this->tenant->id)->count())->toBe(0);
    });
});
