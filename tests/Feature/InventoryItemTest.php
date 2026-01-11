<?php

use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\Pattern;
use App\Models\Tenant;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->for($this->tenant)->create();
    $this->location = InventoryLocation::factory()->for($this->tenant)->create();
    $this->pattern = Pattern::factory()->for($this->tenant)->create();

    $this->actingAs($this->user);
});

it('can list inventory items', function () {
    $items = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->pattern)
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
        ->for($this->pattern)
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
        'sku' => 'TEST001',
        'name' => 'Test Product',
        'description' => 'Test product description',
        'category' => 'garment',
        'pattern_id' => $this->pattern->id,
        'inventory_location_id' => $this->location->id,
        'current_stock' => 100,
        'minimum_stock' => 10,
        'maximum_stock' => 500,
        'unit_cost' => 25.50,
        'selling_price' => 45.00,
        'weight_per_unit' => 0.5,
        'quality_grade' => 'A',
        'status' => 'available',
        'storage_requirements' => 'room_temp',
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
        'sku', 'name', 'category', 'pattern_id',
        'inventory_location_id', 'current_stock', 'unit_cost',
    ]);
});

it('validates unique SKU within tenant', function () {
    InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->pattern)
        ->create(['sku' => 'EXISTING001']);

    $response = $this->post('/inventory/items', [
        'sku' => 'EXISTING001',
        'name' => 'Test Product',
        'category' => 'garment',
        'pattern_id' => $this->pattern->id,
        'inventory_location_id' => $this->location->id,
        'current_stock' => 100,
        'unit_cost' => 25.50,
    ]);

    $response->assertSessionHasErrors(['sku']);
});

it('can update inventory item', function () {
    $item = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->pattern)
        ->create();

    $updateData = [
        'name' => 'Updated Product Name',
        'description' => 'Updated description',
        'category' => 'food',
        'pattern_id' => $this->pattern->id,
        'inventory_location_id' => $this->location->id,
        'current_stock' => 200,
        'minimum_stock' => 20,
        'unit_cost' => 35.00,
        'selling_price' => 55.00,
        'quality_grade' => 'B',
        'status' => 'reserved',
    ];

    $response = $this->put("/inventory/items/{$item->id}", $updateData);

    $response->assertRedirect();

    $this->assertDatabaseHas('inventory_items', array_merge($updateData, [
        'id' => $item->id,
        'tenant_id' => $this->tenant->id,
        'sku' => $item->sku, // SKU should remain unchanged
    ]));
});

it('can delete inventory item', function () {
    $item = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->pattern)
        ->create();

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

    $item = InventoryItem::factory()
        ->for($otherTenant)
        ->for($otherLocation, 'inventoryLocation')
        ->for($otherPattern)
        ->create();

    $response = $this->get("/inventory/items/{$item->id}");

    $response->assertNotFound();
});

it('filters items by status', function () {
    InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->pattern)
        ->create(['status' => 'available']);

    InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->pattern)
        ->create(['status' => 'damaged']);

    $response = $this->get('/inventory/items?status=available');

    $response->assertSuccessful();
    $response->assertInertia(fn (AssertableInertia $page) => $page->component('Inventory/Items/Index')
        ->has('items.data', 1)
        ->where('items.data.0.status', 'available')
    );
});

it('filters items by category', function () {
    InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->pattern)
        ->garment()
        ->create();

    InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->pattern)
        ->food()
        ->create();

    $response = $this->get('/inventory/items?category=garment');

    $response->assertSuccessful();
    $response->assertInertia(fn (AssertableInertia $page) => $page->component('Inventory/Items/Index')
        ->has('items.data', 1)
        ->where('items.data.0.category', 'garment')
    );
});

it('searches items by SKU and name', function () {
    InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->pattern)
        ->create(['sku' => 'ALPHA001', 'name' => 'Alpha Product']);

    InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->pattern)
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
        ->for($this->pattern)
        ->create([
            'current_stock' => 5,
            'minimum_stock' => 10,
        ]);

    $normalStockItem = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->pattern)
        ->create([
            'current_stock' => 50,
            'minimum_stock' => 10,
        ]);

    expect($lowStockItem->is_low_stock)->toBeTrue();
    expect($normalStockItem->is_low_stock)->toBeFalse();
});

it('calculates available stock correctly', function () {
    $item = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->pattern)
        ->create([
            'current_stock' => 100,
            'reserved_stock' => 25,
        ]);

    expect($item->available_stock)->toBe(75);
});

it('handles expiry dates for food items', function () {
    $expiredItem = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->pattern)
        ->food()
        ->expired()
        ->create();

    $freshItem = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->pattern)
        ->food()
        ->create(['expiry_date' => now()->addMonth()]);

    expect($expiredItem->is_expired)->toBeTrue();
    expect($freshItem->is_expired)->toBeFalse();
});

it('calculates days until expiry correctly', function () {
    $item = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->pattern)
        ->food()
        ->create(['expiry_date' => now()->addDays(5)]);

    expect($item->days_until_expiry)->toBe(5);
});

it('handles stock movements and tracking', function () {
    $item = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->pattern)
        ->create([
            'current_stock' => 100,
            'reserved_stock' => 0,
        ]);

    // Test stock reservation
    $item->increment('reserved_stock', 20);
    $item->refresh();

    expect($item->available_stock)->toBe(80);
    expect($item->reserved_stock)->toBe(20);

    // Test stock consumption
    $item->decrement('current_stock', 30);
    $item->decrement('reserved_stock', 20);
    $item->refresh();

    expect($item->current_stock)->toBe(70);
    expect($item->reserved_stock)->toBe(0);
    expect($item->available_stock)->toBe(70);
});
