<?php

use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\Tenant;
use App\Models\User;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->admin()->create(['tenant_id' => $this->tenant->id]);
    $this->actingAs($this->user);
});

test('create page renders', function () {
    // Create necessary data
    Customer::factory()->create(['tenant_id' => $this->tenant->id, 'is_active' => true]);
    InventoryItem::factory()->create(['tenant_id' => $this->tenant->id, 'status' => 'available']);

    $response = $this->get(route('sales-orders.create'));

    $response->assertStatus(200);
});

test('quick checkout page renders', function () {
    InventoryItem::factory()->create(['tenant_id' => $this->tenant->id, 'status' => 'available', 'current_quantity' => 10]);

    $response = $this->get(route('sales-orders.quick-checkout'));

    $response->assertStatus(200);
});

test('quick checkout store successfully registers checkout', function () {
    $item = InventoryItem::factory()->create([
        'tenant_id' => $this->tenant->id,
        'status' => 'available',
        'current_quantity' => 10,
        'reserved_quantity' => 0,
        'selling_price' => 5000,
    ]);

    $postData = [
        'payment_method' => 'cash',
        'items' => [
            [
                'inventory_item_id' => $item->id,
                'quantity' => 2,
                'unit_price' => 5000,
            ],
        ],
    ];

    $response = $this->post(route('sales-orders.quick-checkout.store'), $postData);

    $response->assertRedirect(route('sales-orders.index'));

    $this->assertDatabaseHas('sales_orders', [
        'tenant_id' => $this->tenant->id,
        'total_amount' => 10000,
        'status' => 'completed',
        'payment_status' => 'paid',
    ]);

    $this->assertDatabaseHas('inventory_items', [
        'id' => $item->id,
        'current_quantity' => 8,
    ]);
});

test('quick checkout store rejects order exceeding available stock instead of silently overselling', function () {
    // Prod bug pattern: deductStock() returns false on insufficient stock but
    // quickCheckoutStore ignored it, letting the order become 'completed'
    // and 'paid' with zero units actually deducted.
    $item = InventoryItem::factory()->create([
        'tenant_id' => $this->tenant->id,
        'status' => 'available',
        'current_quantity' => 5,
        'reserved_quantity' => 0,
        'selling_price' => 5000,
    ]);

    $postData = [
        'payment_method' => 'cash',
        'items' => [
            [
                'inventory_item_id' => $item->id,
                'quantity' => 10,
                'unit_price' => 5000,
            ],
        ],
    ];

    $response = $this->post(route('sales-orders.quick-checkout.store'), $postData);

    $response->assertStatus(422);

    $this->assertDatabaseMissing('sales_orders', [
        'tenant_id' => $this->tenant->id,
        'total_amount' => 50000,
    ]);

    $this->assertDatabaseHas('inventory_items', [
        'id' => $item->id,
        'current_quantity' => 5,
    ]);
});
