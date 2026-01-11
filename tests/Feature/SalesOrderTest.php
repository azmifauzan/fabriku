<?php

use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\Pattern;
use App\Models\SalesOrder;
use App\Models\Tenant;
use App\Models\User;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->actingAs($this->user);

    // Create dependencies
    $this->customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->location = InventoryLocation::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->pattern = Pattern::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->inventoryItem = InventoryItem::factory()->create([
        'tenant_id' => $this->tenant->id,
        'inventory_location_id' => $this->location->id,
        'pattern_id' => $this->pattern->id,
        'current_stock' => 100,
        'selling_price' => 150000,
    ]);
});

it('can list sales orders', function () {
    SalesOrder::factory(5)->create(['tenant_id' => $this->tenant->id]);

    $response = $this->get(route('sales-orders.index'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('SalesOrders/Index')
        ->has('orders.data', 5)
    );
});

it('can create a sales order with items', function () {
    $orderData = [
        'customer_id' => $this->customer->id,
        'order_date' => now()->toDateString(),
        'channel' => 'offline',
        'status' => 'draft',
        'payment_method' => 'cash',
        'discount_percentage' => 0,
        'discount_amount' => 0,
        'tax_amount' => 0,
        'items' => [
            [
                'inventory_item_id' => $this->inventoryItem->id,
                'quantity' => 2,
                'unit_price' => 150000,
                'discount_amount' => 0,
            ],
        ],
    ];

    $response = $this->post(route('sales-orders.store'), $orderData);

    $response->assertRedirect();
    $this->assertDatabaseHas('sales_orders', [
        'tenant_id' => $this->tenant->id,
        'customer_id' => $this->customer->id,
        'subtotal' => 300000,
        'total_amount' => 300000,
    ]);
    $this->assertDatabaseHas('sales_order_items', [
        'inventory_item_id' => $this->inventoryItem->id,
        'quantity' => 2,
        'unit_price' => 150000,
        'subtotal' => 300000,
    ]);
});

it('auto-generates order number', function () {
    $orderData = [
        'customer_id' => $this->customer->id,
        'order_date' => now()->toDateString(),
        'channel' => 'offline',
        'payment_method' => 'cash',
        'items' => [
            [
                'inventory_item_id' => $this->inventoryItem->id,
                'quantity' => 1,
                'unit_price' => 150000,
                'discount_amount' => 0,
            ],
        ],
    ];

    $this->post(route('sales-orders.store'), $orderData);

    $order = SalesOrder::latest()->first();
    expect($order->order_number)->toMatch('/^SO-\d{4}-\d{4}$/');
});

it('calculates totals correctly with discount and tax', function () {
    $orderData = [
        'customer_id' => $this->customer->id,
        'order_date' => now()->toDateString(),
        'channel' => 'offline',
        'payment_method' => 'cash',
        'discount_percentage' => 10,
        'tax_amount' => 15000,
        'items' => [
            [
                'inventory_item_id' => $this->inventoryItem->id,
                'quantity' => 2,
                'unit_price' => 150000,
                'discount_amount' => 0,
            ],
        ],
    ];

    $response = $this->post(route('sales-orders.store'), $orderData);

    $response->assertRedirect();
    $this->assertDatabaseHas('sales_orders', [
        'subtotal' => 300000,
        'discount_amount' => 30000, // 10% of 300000
        'tax_amount' => 15000,
        'total_amount' => 285000, // 300000 - 30000 + 15000
    ]);
});

it('can update a draft sales order', function () {
    $order = SalesOrder::factory()->draft()->create([
        'tenant_id' => $this->tenant->id,
        'customer_id' => $this->customer->id,
    ]);

    $updateData = [
        'customer_id' => $this->customer->id,
        'order_date' => now()->toDateString(),
        'channel' => 'online',
        'status' => 'confirmed',
        'payment_method' => 'transfer',
        'discount_percentage' => 0,
        'items' => [
            [
                'inventory_item_id' => $this->inventoryItem->id,
                'quantity' => 3,
                'unit_price' => 150000,
                'discount_amount' => 0,
            ],
        ],
    ];

    $response = $this->put(route('sales-orders.update', $order), $updateData);

    $response->assertRedirect();
    $this->assertDatabaseHas('sales_orders', [
        'id' => $order->id,
        'status' => 'confirmed',
        'channel' => 'online',
    ]);
});

it('cannot edit completed sales order', function () {
    $order = SalesOrder::factory()->completed()->create([
        'tenant_id' => $this->tenant->id,
        'customer_id' => $this->customer->id,
    ]);

    $updateData = [
        'customer_id' => $this->customer->id,
        'order_date' => now()->toDateString(),
        'channel' => 'offline',
        'payment_method' => 'cash',
        'items' => [
            [
                'inventory_item_id' => $this->inventoryItem->id,
                'quantity' => 1,
                'unit_price' => 150000,
                'discount_amount' => 0,
            ],
        ],
    ];

    $response = $this->put(route('sales-orders.update', $order), $updateData);

    $response->assertForbidden();
});

it('can view sales order with items', function () {
    $order = SalesOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'customer_id' => $this->customer->id,
    ]);
    $order->items()->create([
        'inventory_item_id' => $this->inventoryItem->id,
        'quantity' => 2,
        'unit_price' => 150000,
        'discount_amount' => 0,
        'subtotal' => 300000,
    ]);

    $response = $this->get(route('sales-orders.show', $order));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('SalesOrders/Show')
        ->has('order')
        ->has('order.items', 1)
    );
});

it('can delete a draft sales order', function () {
    $order = SalesOrder::factory()->draft()->create([
        'tenant_id' => $this->tenant->id,
        'customer_id' => $this->customer->id,
    ]);

    $response = $this->delete(route('sales-orders.destroy', $order));

    $response->assertRedirect();
    $this->assertDatabaseMissing('sales_orders', ['id' => $order->id]);
});

it('cannot delete completed sales order', function () {
    $order = SalesOrder::factory()->completed()->create([
        'tenant_id' => $this->tenant->id,
        'customer_id' => $this->customer->id,
    ]);

    $response = $this->delete(route('sales-orders.destroy', $order));

    $response->assertSessionHasErrors();
    $this->assertDatabaseHas('sales_orders', ['id' => $order->id]);
});

it('can filter sales orders by status', function () {
    SalesOrder::factory(3)->draft()->create(['tenant_id' => $this->tenant->id]);
    SalesOrder::factory(2)->completed()->create(['tenant_id' => $this->tenant->id]);

    $response = $this->get(route('sales-orders.index', ['status' => 'draft']));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->has('orders.data', 3)
    );
});

it('enforces tenant isolation for sales orders', function () {
    $otherTenant = Tenant::factory()->create();
    $otherOrder = SalesOrder::factory()->create(['tenant_id' => $otherTenant->id]);

    $response = $this->get(route('sales-orders.show', $otherOrder));

    $response->assertNotFound();
});
