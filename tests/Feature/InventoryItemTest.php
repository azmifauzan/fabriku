<?php

use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\Pattern;
use App\Models\PreparationOrder;
use App\Models\ProductionOrder;
use App\Models\Tenant;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->for($this->tenant)->create();
    $this->location = InventoryLocation::factory()->for($this->tenant)->create();
    $this->pattern = Pattern::factory()->for($this->tenant)->create();
    $this->preparationOrder = PreparationOrder::factory()
        ->for($this->tenant)
        ->for($this->pattern)
        ->create(['status' => 'completed']);
    $this->productionOrder = ProductionOrder::factory()
        ->for($this->tenant)
        ->for($this->preparationOrder)
        ->create(['status' => 'completed', 'quantity_good' => 100]);

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
        'location_id' => $this->location->id,
        'target_quantity' => 100,
        'stock_quantity' => 95,
        'minimum_stock' => 10,
        'unit_cost' => 25.50,
        'selling_price' => 45.00,
        'quality_grade' => 'grade_a',
        'status' => 'available',
    ];

    $response = $this->post('/inventory/items', $itemData);

    $response->assertRedirect();

    $this->assertDatabaseHas('inventory_items', array_merge($itemData, [
        'tenant_id' => $this->tenant->id,
    ]));
});

it('validates required fields when creating item', function () {
    $response = $this->post('/inventory/items', []);

    $response->assertSessionHasErrors([
        'production_order_id', 'sku', 'name',
        'location_id', 'target_quantity', 'stock_quantity', 'unit_cost',
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
        'location_id' => $this->location->id,
        'target_quantity' => 100,
        'stock_quantity' => 100,
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
        'name' => 'Updated Product Name',
        'stock_quantity' => 200,
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
        ->create(['reserved_stock' => 0]);

    $response = $this->delete("/inventory/items/{$item->id}");

    $response->assertRedirect();

    $this->assertDatabaseMissing('inventory_items', [
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
        ]);
    $otherProductionOrder = ProductionOrder::factory()
        ->for($otherTenant)
        ->for($otherPreparationOrder)
        ->create(['status' => 'completed', 'quantity_good' => 100]);

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
        ->create(['sku' => 'ALPHA001', 'name' => 'Alpha Product']);

    InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create(['sku' => 'BETA002', 'name' => 'Beta Product']);

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
            'stock_quantity' => 5,
            'minimum_stock' => 10,
        ]);

    $normalStockItem = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create([
            'stock_quantity' => 50,
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
            'stock_quantity' => 100,
            'reserved_stock' => 25,
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
            'stock_quantity' => 100,
            'reserved_stock' => 0,
        ]);

    // Test stock reservation
    $item->increment('reserved_stock', 20);
    $item->refresh();

    expect($item->available_stock)->toBe(80);
    expect($item->reserved_stock)->toBe(20);

    // Test stock consumption
    $item->decrement('stock_quantity', 30);
    $item->decrement('reserved_stock', 20);
    $item->refresh();

    expect($item->stock_quantity)->toBe(70);
    expect($item->reserved_stock)->toBe(0);
    expect($item->available_stock)->toBe(70);
});
