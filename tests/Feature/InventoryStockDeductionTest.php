<?php

use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\SalesOrder;
use App\Models\Tenant;
use App\Models\User;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->actingAs($this->user);

    $this->customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->location = InventoryLocation::factory()->create(['tenant_id' => $this->tenant->id]);

    $this->inventoryItem = InventoryItem::factory()->create([
        'tenant_id' => $this->tenant->id,
        'location_id' => $this->location->id,
        'current_quantity' => 100,
        'reserved_quantity' => 0,
        'minimum_stock' => 10,
        'selling_price' => 150000,
    ]);
});

it('reserves stock when sales order is confirmed', function () {
    $order = SalesOrder::factory()->draft()->create([
        'tenant_id' => $this->tenant->id,
        'customer_id' => $this->customer->id,
    ]);

    $order->items()->create([
        'inventory_item_id' => $this->inventoryItem->id,
        'quantity' => 10,
        'unit_price' => 150000,
        'discount_amount' => 0,
        'subtotal' => 1500000,
    ]);

    // Initially no reservation
    expect($this->inventoryItem->fresh()->current_quantity)->toBe(100);
    expect($this->inventoryItem->fresh()->reserved_quantity)->toBe(0);

    // Confirm order - should reserve stock
    $order->update(['status' => 'confirmed']);

    $this->inventoryItem->refresh();
    expect($this->inventoryItem->current_quantity)->toBe(100)
        ->and($this->inventoryItem->reserved_quantity)->toBe(10);
});

it('deducts stock when sales order is completed from confirmed', function () {
    // Create as draft first
    $order = SalesOrder::factory()->draft()->create([
        'tenant_id' => $this->tenant->id,
        'customer_id' => $this->customer->id,
    ]);

    $order->items()->create([
        'inventory_item_id' => $this->inventoryItem->id,
        'quantity' => 15,
        'unit_price' => 150000,
        'discount_amount' => 0,
        'subtotal' => 2250000,
    ]);

    // Confirm the order - this will automatically reserve stock via observer
    $order->update(['status' => 'confirmed']);

    expect($this->inventoryItem->fresh()->current_quantity)->toBe(100);
    expect($this->inventoryItem->fresh()->reserved_quantity)->toBe(15);

    // Complete order - should deduct from both current and reserved
    $order->update(['status' => 'completed']);

    $this->inventoryItem->refresh();
    expect($this->inventoryItem->current_quantity)->toBe(85)
        ->and($this->inventoryItem->reserved_quantity)->toBe(0);
});

it('deducts stock when sales order is completed', function () {
    // Create as draft first
    $order = SalesOrder::factory()->draft()->create([
        'tenant_id' => $this->tenant->id,
        'customer_id' => $this->customer->id,
    ]);

    $order->items()->create([
        'inventory_item_id' => $this->inventoryItem->id,
        'quantity' => 20,
        'unit_price' => 150000,
        'discount_amount' => 0,
        'subtotal' => 3000000,
    ]);

    // Move to processing - this will automatically reserve stock via observer
    $order->update(['status' => 'processing']);

    expect($this->inventoryItem->fresh()->current_quantity)->toBe(100);
    expect($this->inventoryItem->fresh()->reserved_quantity)->toBe(20);

    // Complete order - should deduct from both current and reserved
    $order->update(['status' => 'completed']);

    $this->inventoryItem->refresh();
    expect($this->inventoryItem->current_quantity)->toBe(80)
        ->and($this->inventoryItem->reserved_quantity)->toBe(0);
});

it('releases reserved stock when sales order is cancelled', function () {
    // Create as draft first
    $order = SalesOrder::factory()->draft()->create([
        'tenant_id' => $this->tenant->id,
        'customer_id' => $this->customer->id,
    ]);

    $order->items()->create([
        'inventory_item_id' => $this->inventoryItem->id,
        'quantity' => 10,
        'unit_price' => 150000,
        'discount_amount' => 0,
        'subtotal' => 1500000,
    ]);

    // Confirm the order - this will automatically reserve stock via observer
    $order->update(['status' => 'confirmed']);

    expect($this->inventoryItem->fresh()->current_quantity)->toBe(100);
    expect($this->inventoryItem->fresh()->reserved_quantity)->toBe(10);

    // Cancel order - should release reservation
    $order->update(['status' => 'cancelled']);

    $this->inventoryItem->refresh();
    expect($this->inventoryItem->current_quantity)->toBe(100)
        ->and($this->inventoryItem->reserved_quantity)->toBe(0);
});

it('shows correct stock in inventory report after sales', function () {
    // Create and complete a sale
    $order = SalesOrder::factory()->draft()->create([
        'tenant_id' => $this->tenant->id,
        'customer_id' => $this->customer->id,
    ]);

    $order->items()->create([
        'inventory_item_id' => $this->inventoryItem->id,
        'quantity' => 25,
        'unit_price' => 150000,
        'discount_amount' => 0,
        'subtotal' => 3750000,
    ]);

    // Move through workflow: draft -> confirmed -> completed
    $order->update(['status' => 'confirmed']); // Reserves stock
    $order->update(['status' => 'completed']); // Deducts stock

    // Check actual database values
    $this->inventoryItem->refresh();
    expect($this->inventoryItem->current_quantity)->toBe(75)
        ->and($this->inventoryItem->reserved_quantity)->toBe(0);

    // Check inventory report shows correct stock
    $response = $this->get(route('reports.inventory'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Reports/InventoryReport')
        ->where('items.0.quantity', 75)
        ->where('items.0.reserved_quantity', 0)
        ->where('items.0.available_quantity', 75)
    );
});

it('handles multiple sales orders correctly', function () {
    // First order
    $order1 = SalesOrder::factory()->draft()->create([
        'tenant_id' => $this->tenant->id,
        'customer_id' => $this->customer->id,
    ]);
    $order1->items()->create([
        'inventory_item_id' => $this->inventoryItem->id,
        'quantity' => 10,
        'unit_price' => 150000,
        'discount_amount' => 0,
        'subtotal' => 1500000,
    ]);

    // Second order
    $order2 = SalesOrder::factory()->draft()->create([
        'tenant_id' => $this->tenant->id,
        'customer_id' => $this->customer->id,
    ]);
    $order2->items()->create([
        'inventory_item_id' => $this->inventoryItem->id,
        'quantity' => 15,
        'unit_price' => 150000,
        'discount_amount' => 0,
        'subtotal' => 2250000,
    ]);

    // Confirm both - should reserve 25 total
    $order1->update(['status' => 'confirmed']);
    $order2->update(['status' => 'confirmed']);

    $this->inventoryItem->refresh();
    expect($this->inventoryItem->current_quantity)->toBe(100)
        ->and($this->inventoryItem->reserved_quantity)->toBe(25);

    // Complete first order
    $order1->update(['status' => 'completed']);

    $this->inventoryItem->refresh();
    expect($this->inventoryItem->current_quantity)->toBe(90)
        ->and($this->inventoryItem->reserved_quantity)->toBe(15);

    // Complete second order
    $order2->update(['status' => 'completed']);

    $this->inventoryItem->refresh();
    expect($this->inventoryItem->current_quantity)->toBe(75)
        ->and($this->inventoryItem->reserved_quantity)->toBe(0);
});
