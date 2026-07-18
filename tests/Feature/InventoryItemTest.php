<?php

use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\Pattern;
use App\Models\PreparationOrder;
use App\Models\ProductionOrder;
use App\Models\Tenant;
use App\Models\User;
use App\Services\InventoryService;
use Inertia\Testing\AssertableInertia;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->for($this->tenant)->create();
    $this->location = InventoryLocation::factory()->for($this->tenant)->create();
    $this->pattern = Pattern::factory()->for($this->tenant)->create();
    $this->preparationOrder = PreparationOrder::factory()
        ->for($this->tenant)
        ->for($this->pattern)
        ->create(['status' => 'completed', 'output_quantity' => 100]);
    $this->productionOrder = ProductionOrder::factory()
        ->for($this->tenant)
        ->for($this->preparationOrder)
        ->create(['status' => 'completed']);

    $this->actingAs($this->user);
});

it('can list inventory items', function () {
    $items = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->count(3)
        ->create();

    $response = $this->get('/inventory/items');

    $response->assertSuccessful();
    $response->assertInertia(fn (AssertableInertia $page) => $page->component('Inventory/Items/Index')
        ->has('items.data', 3)
    );
});

it('can show inventory item details', function () {
    $item = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create();

    $response = $this->get("/inventory/items/{$item->id}");

    $response->assertSuccessful();
    $response->assertInertia(fn (AssertableInertia $page) => $page->component('Inventory/Items/Show')
        ->where('item.id', $item->id)
        ->where('item.sku', $item->sku)
    );
});

it('can create new inventory item', function () {
    $itemData = [
        'production_order_id' => $this->productionOrder->id,
        'sku' => 'TEST001',
        'name' => 'Test Product',
        'locations' => [
            ['location_id' => $this->location->id, 'quantity' => 95],
        ],
        'target_quantity' => 100,
        'minimum_stock' => 10,
        'unit_cost' => 25.50,
        'selling_price' => 45.00,
        'quality_grade' => 'grade_a',
        'status' => 'available',
    ];

    $response = $this->post('/inventory/items', $itemData);

    $response->assertRedirect();

    $this->assertDatabaseHas('inventory_items', [
        'production_order_id' => $this->productionOrder->id,
        'sku' => 'TEST001',
        'product_name' => 'Test Product',
        'location_id' => $this->location->id,
        'target_quantity' => 100,
        'current_quantity' => 95,
        'minimum_stock' => 10,
        'unit_cost' => 25.50,
        'selling_price' => 45.00,
        'quality_grade' => 'grade_a',
        'status' => 'available',
        'tenant_id' => $this->tenant->id,
    ]);
});

it('validates required fields when creating item', function () {
    $response = $this->post('/inventory/items', []);

    // production_order_id is no longer required (nullable for manual entry/opening balance)
    // product_name is required when no production_order_id is provided
    $response->assertSessionHasErrors([
        'product_name', 'locations', 'unit_cost',
    ]);
});

it('validates unique SKU within tenant', function () {
    InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create(['sku' => 'EXISTING001']);

    $response = $this->post('/inventory/items', [
        'production_order_id' => $this->productionOrder->id,
        'sku' => 'EXISTING001',
        'name' => 'Test Product',
        'locations' => [
            ['location_id' => $this->location->id, 'quantity' => 100],
        ],
        'target_quantity' => 100,
        'unit_cost' => 25.50,
    ]);

    $response->assertSessionHasErrors(['sku']);
});

it('can update inventory item', function () {
    $item = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create();

    $updateData = [
        'production_order_id' => $this->productionOrder->id,
        'sku' => $item->sku,
        'name' => 'Updated Product Name',
        'location_id' => $this->location->id,
        'target_quantity' => $item->target_quantity,
        'stock_quantity' => 200,
        'minimum_stock' => 20,
        'unit_cost' => 35.00,
        'selling_price' => 55.00,
        'quality_grade' => 'grade_b',
        'status' => 'reserved',
    ];

    $response = $this->put("/inventory/items/{$item->id}", $updateData);

    $response->assertRedirect();

    $this->assertDatabaseHas('inventory_items', [
        'id' => $item->id,
        'tenant_id' => $this->tenant->id,
        'product_name' => 'Updated Product Name',
        'current_quantity' => 200,
        'minimum_stock' => 20,
        'quality_grade' => 'grade_b',
        'status' => 'reserved',
    ]);
});

it('can delete inventory item', function () {
    $item = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create(['reserved_quantity' => 0]);

    $response = $this->delete("/inventory/items/{$item->id}");

    $response->assertRedirect();

    $this->assertSoftDeleted('inventory_items', [
        'id' => $item->id,
    ]);
});

it('cannot access other tenant items', function () {
    $otherTenant = Tenant::factory()->create();
    $otherLocation = InventoryLocation::factory()->for($otherTenant)->create();
    $otherPattern = Pattern::factory()->for($otherTenant)->create();
    $otherPreparationOrder = PreparationOrder::factory()
        ->for($otherTenant)
        ->for($otherPattern)
        ->create([
            'status' => 'completed',
            'order_number' => 'PRP-OTHER-001',
            'output_quantity' => 100,
        ]);
    $otherProductionOrder = ProductionOrder::factory()
        ->for($otherTenant)
        ->for($otherPreparationOrder)
        ->create(['status' => 'completed']);

    $item = InventoryItem::factory()
        ->for($otherTenant)
        ->for($otherLocation, 'inventoryLocation')
        ->for($otherProductionOrder)
        ->create();

    $response = $this->get("/inventory/items/{$item->id}");

    $response->assertNotFound();
});

it('filters items by status', function () {
    InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create(['status' => 'available']);

    InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create(['status' => 'damaged']);

    $response = $this->get('/inventory/items?status=available');

    $response->assertSuccessful();
    $response->assertInertia(fn (AssertableInertia $page) => $page->component('Inventory/Items/Index')
        ->has('items.data', 1)
        ->where('items.data.0.status', 'available')
    );
});

// Test removed: category field no longer exists in new structure

it('searches items by SKU and name', function () {
    InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create(['sku' => 'ALPHA001', 'product_name' => 'Alpha Product']);

    InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create(['sku' => 'BETA002', 'product_name' => 'Beta Product']);

    $response = $this->get('/inventory/items?search=ALPHA');

    $response->assertSuccessful();
    $response->assertInertia(fn (AssertableInertia $page) => $page->component('Inventory/Items/Index')
        ->has('items.data', 1)
        ->where('items.data.0.sku', 'ALPHA001')
    );
});

it('identifies low stock items correctly', function () {
    $lowStockItem = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create([
            'current_quantity' => 5,
            'minimum_stock' => 10,
        ]);

    $normalStockItem = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create([
            'current_quantity' => 50,
            'minimum_stock' => 10,
        ]);

    expect($lowStockItem->is_low_stock)->toBeTrue();
    expect($normalStockItem->is_low_stock)->toBeFalse();
});

it('calculates available stock correctly', function () {
    $item = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create([
            'current_quantity' => 100,
            'reserved_quantity' => 25,
        ]);

    expect($item->available_stock)->toBe(75);
});

// Test removed: expiry_date field no longer exists in new structure

// Test removed: expiry_date field no longer exists in new structure

it('handles stock movements and tracking', function () {
    $item = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create([
            'current_quantity' => 100,
            'reserved_quantity' => 0,
        ]);

    // Test stock reservation
    $item->increment('reserved_quantity', 20);
    $item->refresh();

    expect($item->available_stock)->toBe(80);
    expect($item->reserved_quantity)->toBe(20);

    // Test stock consumption
    $item->decrement('current_quantity', 30);
    $item->decrement('reserved_quantity', 20);
    $item->refresh();

    expect($item->current_quantity)->toBe(70);
    expect($item->reserved_quantity)->toBe(0);
    expect($item->available_stock)->toBe(70);
});

it('exposes available capacity per location on the create form', function () {
    $limitedLocation = InventoryLocation::factory()->for($this->tenant)->create(['capacity' => 300]);

    InventoryItem::factory()
        ->for($this->tenant)
        ->for($limitedLocation, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create(['location_id' => $limitedLocation->id, 'current_quantity' => 250]);

    $response = $this->get('/inventory/items/create');

    $response->assertSuccessful();
    $response->assertInertia(fn (AssertableInertia $page) => $page->component('Inventory/Items/Create')
        ->where('locations', function ($locations) use ($limitedLocation) {
            $match = collect($locations)->firstWhere('id', $limitedLocation->id);

            return $match && $match['available_capacity'] === 50;
        })
    );
});

it('can split stock across multiple racks on create', function () {
    $rackA = InventoryLocation::factory()->for($this->tenant)->create(['capacity' => 300]);
    $rackB = InventoryLocation::factory()->for($this->tenant)->create(['capacity' => 300]);

    $response = $this->post('/inventory/items', [
        'production_order_id' => $this->productionOrder->id,
        'name' => 'Mukena Bali Putih',
        'locations' => [
            ['location_id' => $rackA->id, 'quantity' => 300],
            ['location_id' => $rackB->id, 'quantity' => 100],
        ],
        'target_quantity' => 400,
        'unit_cost' => 25.50,
        'selling_price' => 45.00,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('inventory_items', [
        'product_name' => 'Mukena Bali Putih',
        'location_id' => $rackA->id,
        'current_quantity' => 300,
    ]);

    $this->assertDatabaseHas('inventory_items', [
        'product_name' => 'Mukena Bali Putih',
        'location_id' => $rackB->id,
        'current_quantity' => 100,
    ]);

    expect(InventoryItem::where('product_name', 'Mukena Bali Putih')->count())->toBe(2);
});

it('rejects a split that exceeds rack capacity and creates no rows', function () {
    $smallRack = InventoryLocation::factory()->for($this->tenant)->create(['capacity' => 300]);
    $countBefore = InventoryItem::count();

    $response = $this->post('/inventory/items', [
        'production_order_id' => $this->productionOrder->id,
        'name' => 'Mukena Bali Putih',
        'locations' => [
            ['location_id' => $smallRack->id, 'quantity' => 400],
        ],
        'target_quantity' => 400,
        'unit_cost' => 25.50,
        'selling_price' => 45.00,
    ]);

    $response->assertSessionHasErrors(['locations.0.quantity']);
    expect(InventoryItem::count())->toBe($countBefore);
});

it('rejects duplicate rack within one split submission', function () {
    $response = $this->post('/inventory/items', [
        'production_order_id' => $this->productionOrder->id,
        'name' => 'Mukena Bali Putih',
        'locations' => [
            ['location_id' => $this->location->id, 'quantity' => 50],
            ['location_id' => $this->location->id, 'quantity' => 50],
        ],
        'target_quantity' => 100,
        'unit_cost' => 25.50,
        'selling_price' => 45.00,
    ]);

    $response->assertSessionHasErrors(['locations.0.location_id', 'locations.1.location_id']);
});

it('auto-generates distinct SKUs per rack even when an explicit SKU is submitted for a split', function () {
    $rackA = InventoryLocation::factory()->for($this->tenant)->create(['capacity' => 300]);
    $rackB = InventoryLocation::factory()->for($this->tenant)->create(['capacity' => 300]);

    $response = $this->post('/inventory/items', [
        'production_order_id' => $this->productionOrder->id,
        'sku' => 'SHARED001',
        'name' => 'Mukena Bali Putih',
        'locations' => [
            ['location_id' => $rackA->id, 'quantity' => 200],
            ['location_id' => $rackB->id, 'quantity' => 100],
        ],
        'target_quantity' => 300,
        'unit_cost' => 25.50,
        'selling_price' => 45.00,
    ]);

    $response->assertRedirect();

    $skus = InventoryItem::where('product_name', 'Mukena Bali Putih')->pluck('sku');

    expect($skus)->toHaveCount(2);
    expect($skus->unique())->toHaveCount(2);
    expect($skus)->not->toContain('SHARED001');
});

it('can transfer stock to another location', function () {
    $item = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create([
            'current_quantity' => 100,
            'reserved_quantity' => 0,
        ]);

    $rackA = InventoryLocation::factory()->for($this->tenant)->create(['capacity' => 300]);
    $rackB = InventoryLocation::factory()->for($this->tenant)->create(['capacity' => 300]);

    $response = $this->post("/inventory/items/{$item->id}/transfer", [
        'splits' => [
            ['location_id' => $rackA->id, 'quantity' => 40],
            ['location_id' => $rackB->id, 'quantity' => 30],
        ],
        'reason' => 'Split stock',
        'notes' => 'Test transfer',
    ]);

    $response->assertRedirect();

    $item->refresh();
    expect($item->current_quantity)->toBe(30);

    $this->assertDatabaseHas('inventory_items', [
        'product_name' => $item->product_name,
        'location_id' => $rackA->id,
        'current_quantity' => 40,
    ]);

    $this->assertDatabaseHas('inventory_items', [
        'product_name' => $item->product_name,
        'location_id' => $rackB->id,
        'current_quantity' => 30,
    ]);
});

it('soft deletes the source item when all available stock is transferred and reserved is zero', function () {
    $item = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create([
            'current_quantity' => 100,
            'reserved_quantity' => 0,
        ]);

    $rackA = InventoryLocation::factory()->for($this->tenant)->create(['capacity' => 300]);

    $response = $this->post("/inventory/items/{$item->id}/transfer", [
        'splits' => [
            ['location_id' => $rackA->id, 'quantity' => 100],
        ],
        'reason' => 'Move all',
    ]);

    $response->assertRedirect();
    $this->assertSoftDeleted('inventory_items', ['id' => $item->id]);
});

it('keeps the source item when all available stock is transferred but reserved is greater than zero', function () {
    $item = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create([
            'current_quantity' => 100,
            'reserved_quantity' => 20,
        ]);

    $rackA = InventoryLocation::factory()->for($this->tenant)->create(['capacity' => 300]);

    $response = $this->post("/inventory/items/{$item->id}/transfer", [
        'splits' => [
            ['location_id' => $rackA->id, 'quantity' => 80],
        ],
        'reason' => 'Move available',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('inventory_items', [
        'id' => $item->id,
        'current_quantity' => 20,
        'reserved_quantity' => 20,
        'deleted_at' => null,
    ]);
});

it('rejects transfer if total exceeds available stock', function () {
    $item = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create([
            'current_quantity' => 100,
            'reserved_quantity' => 20,
        ]);

    $rackA = InventoryLocation::factory()->for($this->tenant)->create(['capacity' => 300]);

    $response = $this->post("/inventory/items/{$item->id}/transfer", [
        'splits' => [
            ['location_id' => $rackA->id, 'quantity' => 90],
        ],
        'reason' => 'Move more than available',
    ]);

    $response->assertSessionHasErrors('splits');

    $item->refresh();
    expect($item->current_quantity)->toBe(100);
});

it('rejects transfer if destination rack capacity is insufficient', function () {
    $item = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create([
            'current_quantity' => 100,
            'reserved_quantity' => 0,
        ]);

    $rackA = InventoryLocation::factory()->for($this->tenant)->create(['capacity' => 50]);

    $response = $this->post("/inventory/items/{$item->id}/transfer", [
        'splits' => [
            ['location_id' => $rackA->id, 'quantity' => 60],
        ],
        'reason' => 'Move to small rack',
    ]);

    $response->assertSessionHasErrors('splits.0.quantity');

    $item->refresh();
    expect($item->current_quantity)->toBe(100);
});

it('rejects transfer if duplicate destination rack', function () {
    $item = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create([
            'current_quantity' => 100,
            'reserved_quantity' => 0,
        ]);

    $rackA = InventoryLocation::factory()->for($this->tenant)->create(['capacity' => 300]);

    $response = $this->post("/inventory/items/{$item->id}/transfer", [
        'splits' => [
            ['location_id' => $rackA->id, 'quantity' => 40],
            ['location_id' => $rackA->id, 'quantity' => 20],
        ],
        'reason' => 'Move duplicate',
    ]);

    $response->assertSessionHasErrors(['splits.0.location_id', 'splits.1.location_id']);
});

it('rejects transfer to the same location', function () {
    $item = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create([
            'current_quantity' => 100,
            'reserved_quantity' => 0,
        ]);

    $response = $this->post("/inventory/items/{$item->id}/transfer", [
        'splits' => [
            ['location_id' => $item->location_id, 'quantity' => 40],
        ],
        'reason' => 'Move same',
    ]);

    $response->assertSessionHasErrors('splits.0.location_id');
});

it('cannot transfer another tenant item', function () {
    $otherTenant = Tenant::factory()->create();
    $otherLocation = InventoryLocation::factory()->for($otherTenant)->create();
    $otherItem = InventoryItem::factory()
        ->for($otherTenant)
        ->for($otherLocation, 'inventoryLocation')
        ->create(['current_quantity' => 100, 'reserved_quantity' => 0]);

    $rackA = InventoryLocation::factory()->for($this->tenant)->create(['capacity' => 300]);

    $response = $this->post("/inventory/items/{$otherItem->id}/transfer", [
        'splits' => [
            ['location_id' => $rackA->id, 'quantity' => 10],
        ],
        'reason' => 'Cross tenant attempt',
    ]);

    $response->assertNotFound();
});

it('rejects transfer to another tenant destination location', function () {
    $item = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create(['current_quantity' => 100, 'reserved_quantity' => 0]);

    $otherTenant = Tenant::factory()->create();
    $otherLocation = InventoryLocation::factory()->for($otherTenant)->create(['capacity' => 300]);

    $response = $this->post("/inventory/items/{$item->id}/transfer", [
        'splits' => [
            ['location_id' => $otherLocation->id, 'quantity' => 10],
        ],
        'reason' => 'Cross tenant destination',
    ]);

    $response->assertSessionHasErrors('splits.0.location_id');

    $item->refresh();
    expect($item->current_quantity)->toBe(100);
});

it('creates stock adjustment records for transfer', function () {
    $item = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create([
            'current_quantity' => 100,
            'reserved_quantity' => 0,
        ]);

    $rackA = InventoryLocation::factory()->for($this->tenant)->create(['capacity' => 300]);

    $response = $this->post("/inventory/items/{$item->id}/transfer", [
        'splits' => [
            ['location_id' => $rackA->id, 'quantity' => 40],
        ],
        'reason' => 'Transfer stock',
    ]);

    $this->assertDatabaseHas('stock_adjustments', [
        'inventory_item_id' => $item->id,
        'adjustment_type' => 'transfer',
        'adjustment_quantity' => -40,
    ]);

    $newItem = InventoryItem::where('location_id', $rackA->id)->first();

    $this->assertDatabaseHas('stock_adjustments', [
        'inventory_item_id' => $newItem->id,
        'adjustment_type' => 'transfer',
        'adjustment_quantity' => 40,
    ]);
});

it('copies product_code and image_path to split items on transfer', function () {
    $item = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create([
            'current_quantity' => 100,
            'reserved_quantity' => 0,
            'product_code' => 'PC-001',
            'image_path' => 'tenants/1/inventory/photo.jpg',
        ]);

    $rackA = InventoryLocation::factory()->for($this->tenant)->create(['capacity' => 300]);

    $this->post("/inventory/items/{$item->id}/transfer", [
        'splits' => [
            ['location_id' => $rackA->id, 'quantity' => 40],
        ],
        'reason' => 'Test copy fields',
    ]);

    $this->assertDatabaseHas('inventory_items', [
        'location_id' => $rackA->id,
        'product_code' => 'PC-001',
        'image_path' => 'tenants/1/inventory/photo.jpg',
    ]);
});

it('rejects a transfer at the service layer if reserved stock grew after the item was loaded (TOCTOU)', function () {
    $item = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create([
            'current_quantity' => 10,
            'reserved_quantity' => 4,
        ]);

    $rackA = InventoryLocation::factory()->for($this->tenant)->create(['capacity' => 300]);

    // Simulates a request that validated against a stale available_stock
    // (e.g. before a concurrent sales-order reservation landed) reaching
    // the service directly with a total that now exceeds available_stock.
    expect(fn () => app(InventoryService::class)->transferStock(
        $item,
        [['location_id' => $rackA->id, 'quantity' => 10]],
        'Race condition attempt',
    ))->toThrow(Exception::class);

    $item->refresh();
    expect($item->current_quantity)->toBe(10);
    expect(InventoryItem::where('location_id', $rackA->id)->count())->toBe(0);
});

it('rejects a transfer at the service layer if the destination rack filled up after validation (TOCTOU)', function () {
    $item = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create([
            'current_quantity' => 100,
            'reserved_quantity' => 0,
        ]);

    $rackA = InventoryLocation::factory()->for($this->tenant)->create(['capacity' => 50]);

    InventoryItem::factory()
        ->for($this->tenant)
        ->for($rackA, 'inventoryLocation')
        ->create(['current_quantity' => 45, 'reserved_quantity' => 0]);

    // Rack A now has only 5 available capacity, but the caller passes 10
    // as if it had validated against a stale, larger available_capacity.
    expect(fn () => app(InventoryService::class)->transferStock(
        $item,
        [['location_id' => $rackA->id, 'quantity' => 10]],
        'Race condition attempt',
    ))->toThrow(Exception::class);

    $item->refresh();
    expect($item->current_quantity)->toBe(100);
    expect(InventoryItem::where('location_id', $rackA->id)->count())->toBe(1);
});

it('throws a friendly error instead of a fatal null deref when the item is gone by the time the transfer lock is acquired', function () {
    $item = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create(['current_quantity' => 100, 'reserved_quantity' => 0]);

    $rackA = InventoryLocation::factory()->for($this->tenant)->create(['capacity' => 300]);

    // Simulates a concurrent transfer that already fully drained and
    // soft-deleted this item before this call acquires its lock.
    $item->delete();

    expect(fn () => app(InventoryService::class)->transferStock(
        $item,
        [['location_id' => $rackA->id, 'quantity' => 10]],
        'Race condition attempt',
    ))->toThrow(Exception::class);
});

it('flashes a friendly error instead of a 500 when the transfer service throws', function () {
    $item = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create(['current_quantity' => 100, 'reserved_quantity' => 0]);

    $rackA = InventoryLocation::factory()->for($this->tenant)->create(['capacity' => 300]);

    $this->mock(InventoryService::class, function ($mock) {
        $mock->shouldReceive('transferStock')->once()->andThrow(new Exception('Stok tidak cukup untuk dipindah.'));
    });

    $response = $this->post("/inventory/items/{$item->id}/transfer", [
        'splits' => [
            ['location_id' => $rackA->id, 'quantity' => 10],
        ],
        'reason' => 'Test controller error handling',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error', 'Stok tidak cukup untuk dipindah.');
});

it('can merge two compatible items in the same rack', function () {
    $compatible = [
        'product_name' => 'Kaos Polos M',
        'product_code' => 'PC-KAOS-M',
        'quality_grade' => 'A',
        'unit_cost' => 15000,
        'selling_price' => 25000,
        'status' => 'available',
        'expired_date' => null,
        'reserved_quantity' => 0,
    ];

    $source = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create([...$compatible, 'current_quantity' => 40]);

    $destination = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create([...$compatible, 'current_quantity' => 10]);

    $response = $this->post("/inventory/items/{$source->id}/merge", [
        'destination_item_id' => $destination->id,
        'reason' => 'Konsolidasi rak',
    ]);

    $response->assertRedirect(route('inventory.items.show', $destination));

    $this->assertSoftDeleted('inventory_items', ['id' => $source->id]);

    $destination->refresh();
    expect($destination->current_quantity)->toBe(50);

    $this->assertDatabaseHas('stock_adjustments', [
        'inventory_item_id' => $source->id,
        'adjustment_type' => 'merge',
        'quantity_before' => 40,
        'quantity_after' => 0,
        'adjustment_quantity' => -40,
    ]);

    $this->assertDatabaseHas('stock_adjustments', [
        'inventory_item_id' => $destination->id,
        'adjustment_type' => 'merge',
        'quantity_before' => 10,
        'quantity_after' => 50,
        'adjustment_quantity' => 40,
    ]);
});

it('rejects merge between items that differ on a compatibility field', function () {
    $source = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create(['product_name' => 'Kaos Polos M', 'unit_cost' => 15000, 'current_quantity' => 40, 'reserved_quantity' => 0]);

    $destination = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create(['product_name' => 'Kaos Polos M', 'unit_cost' => 18000, 'current_quantity' => 10, 'reserved_quantity' => 0]);

    $response = $this->post("/inventory/items/{$source->id}/merge", [
        'destination_item_id' => $destination->id,
        'reason' => 'Konsolidasi rak',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error');

    $source->refresh();
    $destination->refresh();
    expect($source->current_quantity)->toBe(40);
    expect($destination->current_quantity)->toBe(10);
});

it('rejects merge when source or destination has reserved stock', function () {
    $compatible = [
        'product_name' => 'Kaos Polos M',
        'unit_cost' => 15000,
        'selling_price' => 25000,
    ];

    $source = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create([...$compatible, 'current_quantity' => 40, 'reserved_quantity' => 5]);

    $destination = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create([...$compatible, 'current_quantity' => 10, 'reserved_quantity' => 0]);

    $response = $this->post("/inventory/items/{$source->id}/merge", [
        'destination_item_id' => $destination->id,
        'reason' => 'Konsolidasi rak',
    ]);

    $response->assertSessionHas('error');
});

it('rejects merge into itself', function () {
    $item = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create(['current_quantity' => 40, 'reserved_quantity' => 0]);

    $response = $this->post("/inventory/items/{$item->id}/merge", [
        'destination_item_id' => $item->id,
        'reason' => 'Konsolidasi rak',
    ]);

    $response->assertSessionHasErrors(['destination_item_id']);
});

it('cannot merge into another tenant item', function () {
    $item = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create(['current_quantity' => 40, 'reserved_quantity' => 0]);

    $otherTenant = Tenant::factory()->create();
    $otherLocation = InventoryLocation::factory()->for($otherTenant)->create();
    $otherItem = InventoryItem::factory()
        ->for($otherTenant)
        ->for($otherLocation, 'inventoryLocation')
        ->create(['current_quantity' => 10, 'reserved_quantity' => 0]);

    $response = $this->post("/inventory/items/{$item->id}/merge", [
        'destination_item_id' => $otherItem->id,
        'reason' => 'Cross tenant attempt',
    ]);

    $response->assertSessionHasErrors(['destination_item_id']);
});

it('only lists merge candidates that are actually compatible', function () {
    $item = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create([
            'product_name' => 'Kaos Polos M',
            'product_code' => 'PC-KAOS-M',
            'unit_cost' => 15000,
            'selling_price' => 25000,
            'quality_grade' => 'A',
            'status' => 'available',
            'expired_date' => null,
            'current_quantity' => 40,
            'reserved_quantity' => 0,
        ]);

    $compatible = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create([
            'product_name' => 'Kaos Polos M',
            'product_code' => 'PC-KAOS-M',
            'unit_cost' => 15000,
            'selling_price' => 25000,
            'quality_grade' => 'A',
            'status' => 'available',
            'expired_date' => null,
            'current_quantity' => 10,
            'reserved_quantity' => 0,
        ]);

    // Same product/rack but different unit_cost - must NOT be offered as a candidate.
    $incompatible = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create([
            'product_name' => 'Kaos Polos M',
            'product_code' => 'PC-KAOS-M',
            'unit_cost' => 18000,
            'selling_price' => 25000,
            'quality_grade' => 'A',
            'status' => 'available',
            'expired_date' => null,
            'current_quantity' => 10,
            'reserved_quantity' => 0,
        ]);

    $response = $this->get("/inventory/items/{$item->id}");

    $response->assertInertia(fn (AssertableInertia $page) => $page->component('Inventory/Items/Show')
        ->where('mergeCandidates', function ($candidates) use ($compatible, $incompatible) {
            $ids = collect($candidates)->pluck('id');

            return $ids->contains($compatible->id) && ! $ids->contains($incompatible->id);
        })
    );
});
