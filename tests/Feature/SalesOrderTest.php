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
    $this->user = User::factory()->admin()->create(['tenant_id' => $this->tenant->id]);
    $this->actingAs($this->user);

    // Create dependencies
    $this->customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->location = InventoryLocation::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->pattern = Pattern::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->inventoryItem = InventoryItem::factory()->create([
        'tenant_id' => $this->tenant->id,
        'location_id' => $this->location->id,
        'current_quantity' => 100,
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

it('cannot edit completed sales order even when unpaid (use updatePayment instead)', function () {
    $order = SalesOrder::factory()->completed()->create([
        'tenant_id' => $this->tenant->id,
        'customer_id' => $this->customer->id,
        'payment_status' => 'unpaid',
        'paid_amount' => 0,
    ]);

    $updateData = [
        'customer_id' => $this->customer->id,
        'order_date' => now()->toDateString(),
        'channel' => 'offline',
        'payment_method' => 'transfer',
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

    // Completed orders are locked for editing — use updatePayment or updateStatus instead
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
        ->has('salesOrder')
        ->has('salesOrder.items', 1)
    );
});

it('can delete a draft sales order', function () {
    $order = SalesOrder::factory()->draft()->create([
        'tenant_id' => $this->tenant->id,
        'customer_id' => $this->customer->id,
    ]);

    $response = $this->delete(route('sales-orders.destroy', $order));

    $response->assertRedirect();
    $this->assertSoftDeleted('sales_orders', ['id' => $order->id]);
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

it('can print invoice for a sales order', function () {
    $order = SalesOrder::factory()->create(['tenant_id' => $this->tenant->id]);

    $response = $this->get(route('sales-orders.invoice', $order));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('SalesOrders/Invoice')
        ->has('salesOrder')
        ->has('settings')
    );
});

it('can view delivery order (surat jalan) for a sales order', function () {
    $order = SalesOrder::factory()->create(['tenant_id' => $this->tenant->id]);

    $response = $this->get(route('sales-orders.delivery-order', $order));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('SalesOrders/DeliveryOrder')
        ->has('salesOrder')
        ->has('settings')
    );
});

it('cannot access delivery order of another tenant', function () {
    $otherTenant = Tenant::factory()->create();
    $otherOrder = SalesOrder::factory()->create(['tenant_id' => $otherTenant->id]);

    $response = $this->get(route('sales-orders.delivery-order', $otherOrder));

    $response->assertNotFound();
});

it('does not reserve stock when creating a draft sales order', function () {
    $this->inventoryItem->update(['current_quantity' => 50, 'reserved_quantity' => 0]);

    $orderData = [
        'customer_id' => $this->customer->id,
        'order_date' => now()->toDateString(),
        'channel' => 'offline',
        'payment_method' => 'cash',
        'items' => [
            [
                'inventory_item_id' => $this->inventoryItem->id,
                'quantity' => 10,
                'unit_price' => 150000,
                'discount_amount' => 0,
            ],
        ],
    ];

    $this->post(route('sales-orders.store'), $orderData);

    // Draft creation does not reserve stock; observer handles reservation on status → confirmed
    $this->assertDatabaseHas('inventory_items', [
        'id' => $this->inventoryItem->id,
        'reserved_quantity' => 0,
    ]);
});

it('reserves stock when sales order status changes to confirmed', function () {
    $this->inventoryItem->update(['current_quantity' => 50, 'reserved_quantity' => 0]);

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

    $order->update(['status' => 'confirmed']);

    $this->assertDatabaseHas('inventory_items', [
        'id' => $this->inventoryItem->id,
        'reserved_quantity' => 10,
    ]);
});

it('rejects order when quantity exceeds available stock', function () {
    $this->inventoryItem->update(['current_quantity' => 45, 'reserved_quantity' => 5]);

    $orderData = [
        'customer_id' => $this->customer->id,
        'order_date' => now()->toDateString(),
        'channel' => 'offline',
        'payment_method' => 'cash',
        'items' => [
            [
                'inventory_item_id' => $this->inventoryItem->id,
                'quantity' => 45,
                'unit_price' => 150000,
                'discount_amount' => 0,
            ],
        ],
    ];

    $response = $this->post(route('sales-orders.store'), $orderData);

    $response->assertSessionHasErrors('items.0.quantity');
    $this->assertDatabaseCount('sales_orders', 0);
    $this->assertDatabaseHas('inventory_items', [
        'id' => $this->inventoryItem->id,
        'reserved_quantity' => 5,
    ]);
});

it('allows order when quantity matches available stock exactly', function () {
    $this->inventoryItem->update(['current_quantity' => 45, 'reserved_quantity' => 5]);

    $orderData = [
        'customer_id' => $this->customer->id,
        'order_date' => now()->toDateString(),
        'channel' => 'offline',
        'payment_method' => 'cash',
        'items' => [
            [
                'inventory_item_id' => $this->inventoryItem->id,
                'quantity' => 40, // exactly available_stock (45 - 5 = 40)
                'unit_price' => 150000,
                'discount_amount' => 0,
            ],
        ],
    ];

    $response = $this->post(route('sales-orders.store'), $orderData);

    $response->assertRedirect();
    // Draft creation does not reserve; reserved_quantity stays unchanged
    $this->assertDatabaseHas('inventory_items', [
        'id' => $this->inventoryItem->id,
        'reserved_quantity' => 5,
    ]);
});

it('releases reserved stock when deleting a confirmed sales order', function () {
    $this->inventoryItem->update(['current_quantity' => 50, 'reserved_quantity' => 0]);

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

    // Confirm the order → observer reserves stock
    $order->update(['status' => 'confirmed']);
    $this->inventoryItem->refresh();
    expect($this->inventoryItem->reserved_quantity)->toBe(10);

    // Delete confirmed order → observer releases stock
    $this->delete(route('sales-orders.destroy', $order));

    $this->assertDatabaseHas('inventory_items', [
        'id' => $this->inventoryItem->id,
        'reserved_quantity' => 0,
    ]);
});

it('releases reserved stock when force deleting a confirmed order not yet soft-deleted', function () {
    $this->inventoryItem->update(['current_quantity' => 50, 'reserved_quantity' => 0]);

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

    $order->update(['status' => 'confirmed']);
    $this->inventoryItem->refresh();
    expect($this->inventoryItem->reserved_quantity)->toBe(10);

    // Force delete a non-trashed confirmed order → observer releases stock
    $order->forceDelete();

    $this->assertDatabaseHas('inventory_items', [
        'id' => $this->inventoryItem->id,
        'reserved_quantity' => 0,
    ]);
});

it('does not double-release reserved stock when force deleting an already soft-deleted order', function () {
    $this->inventoryItem->update(['current_quantity' => 50, 'reserved_quantity' => 0]);

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

    $order->update(['status' => 'confirmed']);

    // Soft delete releases stock via deleted observer
    $order->delete();
    $this->inventoryItem->refresh();
    expect($this->inventoryItem->reserved_quantity)->toBe(0);

    // Force delete a trashed order → must NOT release again (would go negative)
    $order->forceDelete();

    $this->assertDatabaseHas('inventory_items', [
        'id' => $this->inventoryItem->id,
        'reserved_quantity' => 0,
    ]);
});

it('does not adjust reserved stock when updating a draft sales order items', function () {
    $this->inventoryItem->update(['current_quantity' => 50, 'reserved_quantity' => 0]);

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

    $updateData = [
        'customer_id' => $this->customer->id,
        'order_date' => now()->toDateString(),
        'channel' => 'offline',
        'payment_method' => 'cash',
        'items' => [
            [
                'inventory_item_id' => $this->inventoryItem->id,
                'quantity' => 5,
                'unit_price' => 150000,
                'discount_amount' => 0,
            ],
        ],
    ];

    $this->put(route('sales-orders.update', $order), $updateData);

    // Draft order item updates do not affect reserved_quantity (observer handles via status transitions)
    $this->assertDatabaseHas('inventory_items', [
        'id' => $this->inventoryItem->id,
        'reserved_quantity' => 0,
    ]);
});

it('calculates totals correctly with shipping cost', function () {
    $orderData = [
        'customer_id' => $this->customer->id,
        'order_date' => now()->toDateString(),
        'channel' => 'offline',
        'payment_method' => 'cash',
        'shipping_cost' => 25000,
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
        'shipping_cost' => 25000,
        'total_amount' => 325000,
    ]);
});

it('stores payment due date correctly', function () {
    $dueDate = now()->addDays(14)->toDateString();
    $orderData = [
        'customer_id' => $this->customer->id,
        'order_date' => now()->toDateString(),
        'payment_due_date' => $dueDate,
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

    $response = $this->post(route('sales-orders.store'), $orderData);

    $response->assertRedirect();

    $order = SalesOrder::latest('id')->first();
    expect($order->customer_id)->toBe($this->customer->id);
    expect($order->payment_due_date?->toDateString())->toBe($dueDate);
});

it('auto-generates invoice number when transitioning to confirmed', function () {
    $order = SalesOrder::factory()->draft()->create([
        'tenant_id' => $this->tenant->id,
        'customer_id' => $this->customer->id,
        'invoice_number' => null,
    ]);
    $order->items()->create([
        'inventory_item_id' => $this->inventoryItem->id,
        'quantity' => 2,
        'unit_price' => 150000,
        'subtotal' => 300000,
    ]);

    $response = $this->patch(route('sales-orders.update-status', $order), [
        'status' => 'confirmed',
    ]);

    $response->assertRedirect();
    $order->refresh();

    $year = now()->year;
    $month = str_pad(now()->month, 2, '0', STR_PAD_LEFT);
    expect($order->invoice_number)->toMatch("/^INV\/{$year}\/{$month}\/\d{4}$/");
});

it('can filter sales orders by overdue payment status', function () {
    $overdueOrder = SalesOrder::factory()->draft()->create([
        'tenant_id' => $this->tenant->id,
        'customer_id' => $this->customer->id,
        'payment_status' => 'unpaid',
        'payment_due_date' => now()->subDays(5)->toDateString(),
    ]);

    $notOverdueOrder = SalesOrder::factory()->draft()->create([
        'tenant_id' => $this->tenant->id,
        'customer_id' => $this->customer->id,
        'payment_status' => 'unpaid',
        'payment_due_date' => now()->addDays(5)->toDateString(),
    ]);

    $paidPastDueOrder = SalesOrder::factory()->draft()->create([
        'tenant_id' => $this->tenant->id,
        'customer_id' => $this->customer->id,
        'payment_status' => 'paid',
        'payment_due_date' => now()->subDays(5)->toDateString(),
    ]);

    $response = $this->get(route('sales-orders.index', ['payment_status' => 'overdue']));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->has('orders.data', 1)
        ->where('orders.data.0.id', $overdueOrder->id)
    );
});
