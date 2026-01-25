<?php

use App\Models\User;
use App\Models\SalesOrder;
use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\Tenant;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->actingAs($this->user);
    
    $this->inventoryItem = InventoryItem::factory()->create([
        'tenant_id' => $this->tenant->id,
        'current_quantity' => 100,
        'reserved_quantity' => 0,
        'status' => 'available'
    ]);
});

test('soft deleting confirmed order releases reserved stock', function () {
    $order = SalesOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'status' => 'confirmed',
    ]);
    
    $order->items()->create([
        'inventory_item_id' => $this->inventoryItem->id,
        'quantity' => 10,
        'unit_price' => 1000,
        'subtotal' => 10000,
    ]);

    $this->inventoryItem->refresh();
    expect($this->inventoryItem->reserved_quantity)->toBe(10);

    // Soft Delete
    $order->delete();

    $this->inventoryItem->refresh();
    expect($this->inventoryItem->reserved_quantity)->toBe(0);
});

test('restoring confirmed order reserves stock', function () {
    $order = SalesOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'status' => 'confirmed',
    ]);
    
    $order->items()->create([
        'inventory_item_id' => $this->inventoryItem->id,
        'quantity' => 10,
        'unit_price' => 1000,
        'subtotal' => 10000,
    ]);

    $order->delete(); // Releases stock
    $this->inventoryItem->refresh();
    expect($this->inventoryItem->reserved_quantity)->toBe(0);

    // Restore
    $order->restore();

    $this->inventoryItem->refresh();
    expect($this->inventoryItem->reserved_quantity)->toBe(10);
});

test('force deleting confirmed order releases reserved stock', function () {
    $order = SalesOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'status' => 'confirmed',
    ]);
    
    $order->items()->create([
        'inventory_item_id' => $this->inventoryItem->id,
        'quantity' => 10,
        'unit_price' => 1000,
        'subtotal' => 10000,
    ]);

    $this->inventoryItem->refresh();
    expect($this->inventoryItem->reserved_quantity)->toBe(10);

    // Force Delete
    $order->forceDelete();

    $this->inventoryItem->refresh();
    expect($this->inventoryItem->reserved_quantity)->toBe(0);
});

test('force deleting already soft deleted order does not double release', function () {
    $order = SalesOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'status' => 'confirmed',
    ]);
    
    $order->items()->create([
        'inventory_item_id' => $this->inventoryItem->id,
        'quantity' => 10,
        'unit_price' => 1000,
        'subtotal' => 10000,
    ]);

    // Soft Delete (-10)
    $order->delete();
    $this->inventoryItem->refresh();
    expect($this->inventoryItem->reserved_quantity)->toBe(0);

    // Force Delete (Should not deduct again)
    $order->forceDelete();

    $this->inventoryItem->refresh();
    expect($this->inventoryItem->reserved_quantity)->toBe(0);
});
