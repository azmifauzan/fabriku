<?php

use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\Pattern;
use App\Models\PreparationOrder;
use App\Models\ProductionOrder;
use App\Models\StockAdjustment;
use App\Models\Tenant;
use App\Models\User;

uses()->group('inventory');

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

it('can create inventory item with manual entry (opening balance)', function () {
    $response = $this->post('/inventory/items', [
        'product_name' => 'Manual Stock Item',
        'sku' => 'MANUAL001',
        'location_id' => $this->location->id,
        'target_quantity' => 100,
        'current_quantity' => 50,
        'unit_cost' => 25.00,
        'source_type' => 'opening_balance',
        'quality_grade' => 'grade_a',
    ]);

    $response->assertRedirectToRoute('inventory.items.show', ['item' => 1]);

    $this->assertDatabaseHas('inventory_items', [
        'product_name' => 'Manual Stock Item',
        'sku' => 'MANUAL001',
        'production_order_id' => null,
        'source_type' => 'opening_balance',
        'tenant_id' => $this->tenant->id,
    ]);
});

it('can create inventory item from production order', function () {
    $response = $this->post('/inventory/items', [
        'production_order_id' => $this->productionOrder->id,
        'location_id' => $this->location->id,
        'target_quantity' => 100,
        'current_quantity' => 100,
        'unit_cost' => 30.00,
        'quality_grade' => 'grade_a',
    ]);

    $response->assertRedirectToRoute('inventory.items.show', ['item' => 1]);

    $this->assertDatabaseHas('inventory_items', [
        'production_order_id' => $this->productionOrder->id,
        'source_type' => 'production',
        'tenant_id' => $this->tenant->id,
    ]);
});

it('can adjust stock quantity of inventory item', function () {
    $item = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create(['current_quantity' => 100]);

    $response = $this->post("/inventory/items/{$item->id}/adjust", [
        'type' => 'add',
        'quantity' => 25,
        'adjustment_type' => 'correction',
        'reason' => 'Stock count adjustment',
    ]);

    $response->assertRedirect();

    $item->refresh();
    expect($item->current_quantity)->toBe(125);

    $this->assertDatabaseHas('stock_adjustments', [
        'inventory_item_id' => $item->id,
        'adjustment_type' => 'correction',
        'quantity_before' => 100,
        'quantity_after' => 125,
        'adjustment_quantity' => 25,
        'tenant_id' => $this->tenant->id,
    ]);
});

it('can subtract stock from inventory item', function () {
    $item = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create(['current_quantity' => 100]);

    $response = $this->post("/inventory/items/{$item->id}/adjust", [
        'type' => 'subtract',
        'quantity' => 30,
        'adjustment_type' => 'correction',
        'reason' => 'Damaged goods',
    ]);

    $response->assertRedirect();

    $item->refresh();
    expect($item->current_quantity)->toBe(70);
});

it('can set stock to specific quantity', function () {
    $item = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create(['current_quantity' => 100]);

    $response = $this->post("/inventory/items/{$item->id}/adjust", [
        'type' => 'set',
        'quantity' => 75,
        'adjustment_type' => 'correction',
        'reason' => 'Physical count adjustment',
    ]);

    $response->assertRedirect();

    $item->refresh();
    expect($item->current_quantity)->toBe(75);
});

it('prevents negative stock on subtract operation', function () {
    $item = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create(['current_quantity' => 10]);

    $response = $this->post("/inventory/items/{$item->id}/adjust", [
        'type' => 'subtract',
        'quantity' => 20,
        'adjustment_type' => 'correction',
        'reason' => 'Test negative',
    ]);

    $response->assertSessionHasErrors(['quantity']);
});

it('can view adjustment history for inventory item', function () {
    $item = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create(['current_quantity' => 100]);

    // Create some adjustments
    StockAdjustment::factory()
        ->for($this->tenant)
        ->for($item, 'inventoryItem')
        ->for($this->user, 'adjustedBy')
        ->count(3)
        ->create();

    // Test as JSON request to avoid Vite manifest issue in tests
    $response = $this->getJson("/inventory/items/{$item->id}/adjustments");

    // When accessed as JSON, it should return Inertia response with the adjustments data
    $response->assertOk();
})->skip('Requires frontend build');

it('records user who made the adjustment', function () {
    $item = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create(['current_quantity' => 100]);

    $this->post("/inventory/items/{$item->id}/adjust", [
        'type' => 'add',
        'quantity' => 10,
        'adjustment_type' => 'correction',
        'reason' => 'Test adjustment',
    ]);

    $this->assertDatabaseHas('stock_adjustments', [
        'inventory_item_id' => $item->id,
        'adjusted_by' => $this->user->id,
    ]);
});

it('validates source_type for manual entry', function () {
    $response = $this->post('/inventory/items', [
        'product_name' => 'Manual Stock Item',
        'sku' => 'MANUAL002',
        'location_id' => $this->location->id,
        'target_quantity' => 100,
        'current_quantity' => 50,
        'unit_cost' => 25.00,
        'source_type' => 'invalid_type', // Invalid type
        'quality_grade' => 'grade_a',
    ]);

    $response->assertSessionHasErrors(['source_type']);
});

it('helper method isManualEntry works correctly', function () {
    $manualItem = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->create([
            'production_order_id' => null,
            'source_type' => 'opening_balance',
        ]);

    $productionItem = InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create([
            'source_type' => 'production',
        ]);

    expect($manualItem->isManualEntry())->toBeTrue();
    expect($productionItem->isManualEntry())->toBeFalse();
    expect($productionItem->isFromProduction())->toBeTrue();
});
