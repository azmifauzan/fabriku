<?php

use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\Tenant;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create(['business_category' => 'garment']);
    $this->location = InventoryLocation::factory()->create(['tenant_id' => $this->tenant->id]);
});

it('generates SKU with category-specific prefix from config', function () {
    // Garment category prefix is INV-GRM
    $item = InventoryItem::factory()->create([
        'tenant_id' => $this->tenant->id,
        'location_id' => $this->location->id,
        'sku' => null,
    ]);

    expect($item->sku)->toBe('INV-GRM-0001');

    // Food category prefix is INV-FOOD
    $foodTenant = Tenant::factory()->create(['business_category' => 'food']);
    $foodLocation = InventoryLocation::factory()->create(['tenant_id' => $foodTenant->id]);
    $foodItem = InventoryItem::factory()->create([
        'tenant_id' => $foodTenant->id,
        'location_id' => $foodLocation->id,
        'sku' => null,
    ]);

    expect($foodItem->sku)->toBe('INV-FOOD-0001');
});

it('auto-increments sequential SKU numbers', function () {
    $item1 = InventoryItem::factory()->create([
        'tenant_id' => $this->tenant->id,
        'location_id' => $this->location->id,
        'sku' => null,
    ]);

    $item2 = InventoryItem::factory()->create([
        'tenant_id' => $this->tenant->id,
        'location_id' => $this->location->id,
        'sku' => null,
    ]);

    $item3 = InventoryItem::factory()->create([
        'tenant_id' => $this->tenant->id,
        'location_id' => $this->location->id,
        'sku' => null,
    ]);

    expect($item1->sku)->toBe('INV-GRM-0001');
    expect($item2->sku)->toBe('INV-GRM-0002');
    expect($item3->sku)->toBe('INV-GRM-0003');
});

it('skips existing SKUs to avoid duplicates', function () {
    // Manually create item with INV-GRM-0001
    InventoryItem::factory()->create([
        'tenant_id' => $this->tenant->id,
        'location_id' => $this->location->id,
        'sku' => 'INV-GRM-0001',
    ]);

    // Create item with INV-GRM-0002 explicitly
    InventoryItem::factory()->create([
        'tenant_id' => $this->tenant->id,
        'location_id' => $this->location->id,
        'sku' => 'INV-GRM-0002',
    ]);

    // System should generate INV-GRM-0003 for next item
    $item = InventoryItem::factory()->create([
        'tenant_id' => $this->tenant->id,
        'location_id' => $this->location->id,
        'sku' => null,
    ]);

    expect($item->sku)->toBe('INV-GRM-0003');
});

it('skips past a soft-deleted item still holding a SKU', function () {
    $item = InventoryItem::factory()->create([
        'tenant_id' => $this->tenant->id,
        'location_id' => $this->location->id,
        'sku' => 'INV-GRM-0001',
    ]);
    $item->delete();

    $next = InventoryItem::factory()->create([
        'tenant_id' => $this->tenant->id,
        'location_id' => $this->location->id,
        'sku' => null,
    ]);

    expect($next->sku)->toBe('INV-GRM-0002');
});
