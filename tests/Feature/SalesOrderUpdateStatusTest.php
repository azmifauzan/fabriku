<?php

use App\Models\InventoryItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Tenant;
use App\Models\User;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->admin()->create(['tenant_id' => $this->tenant->id]);
    $this->actingAs($this->user);
});

// ─────────────────────────────────────────────────────────
// Positive: valid transitions
// ─────────────────────────────────────────────────────────

test('transitions processing to shipped', function () {
    $salesOrder = SalesOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'status' => 'processing',
    ]);

    $response = $this->patch(route('sales-orders.update-status', $salesOrder), [
        'status' => 'shipped',
        'resi_number' => 'JNE1234567890',
    ]);

    $response->assertRedirect(route('sales-orders.show', $salesOrder));

    $this->assertDatabaseHas('sales_orders', [
        'id' => $salesOrder->id,
        'status' => 'shipped',
        'resi_number' => 'JNE1234567890',
    ]);
});

test('transitions processing to completed', function () {
    $salesOrder = SalesOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'status' => 'processing',
    ]);

    $response = $this->patch(route('sales-orders.update-status', $salesOrder), [
        'status' => 'completed',
    ]);

    $response->assertRedirect(route('sales-orders.show', $salesOrder));

    $this->assertDatabaseHas('sales_orders', [
        'id' => $salesOrder->id,
        'status' => 'completed',
    ]);
});

test('transitions shipped to completed', function () {
    $salesOrder = SalesOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'status' => 'shipped',
    ]);

    $response = $this->patch(route('sales-orders.update-status', $salesOrder), [
        'status' => 'completed',
    ]);

    $response->assertRedirect(route('sales-orders.show', $salesOrder));

    $this->assertDatabaseHas('sales_orders', [
        'id' => $salesOrder->id,
        'status' => 'completed',
    ]);
});

test('transitions shipped to cancelled', function () {
    $salesOrder = SalesOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'status' => 'shipped',
    ]);

    $response = $this->patch(route('sales-orders.update-status', $salesOrder), [
        'status' => 'cancelled',
    ]);

    $response->assertRedirect(route('sales-orders.show', $salesOrder));

    $this->assertDatabaseHas('sales_orders', [
        'id' => $salesOrder->id,
        'status' => 'cancelled',
    ]);
});

test('transitions confirmed to processing', function () {
    $salesOrder = SalesOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'status' => 'confirmed',
    ]);

    $response = $this->patch(route('sales-orders.update-status', $salesOrder), [
        'status' => 'processing',
    ]);

    $response->assertRedirect(route('sales-orders.show', $salesOrder));

    $this->assertDatabaseHas('sales_orders', [
        'id' => $salesOrder->id,
        'status' => 'processing',
    ]);
});

// ─────────────────────────────────────────────────────────
// Negative: invalid transitions
// ─────────────────────────────────────────────────────────

test('rejects invalid transition from completed to draft', function () {
    $salesOrder = SalesOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'status' => 'completed',
    ]);

    $response = $this->patch(route('sales-orders.update-status', $salesOrder), [
        'status' => 'draft',
    ]);

    $response->assertStatus(422);

    $this->assertDatabaseHas('sales_orders', [
        'id' => $salesOrder->id,
        'status' => 'completed',
    ]);
});

test('rejects invalid transition from cancelled to confirmed', function () {
    $salesOrder = SalesOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'status' => 'cancelled',
    ]);

    $response = $this->patch(route('sales-orders.update-status', $salesOrder), [
        'status' => 'confirmed',
    ]);

    $response->assertStatus(422);
});

test('rejects invalid status value', function () {
    $salesOrder = SalesOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'status' => 'processing',
    ]);

    $response = $this->patch(route('sales-orders.update-status', $salesOrder), [
        'status' => 'invalid-status',
    ]);

    $response->assertSessionHasErrors('status');
});

// ─────────────────────────────────────────────────────────
// Observer fix: stock integrity
// ─────────────────────────────────────────────────────────

test('deducts stock when transitioning shipped to completed', function () {
    $inventoryItem = InventoryItem::factory()->create([
        'tenant_id' => $this->tenant->id,
        'current_quantity' => 10,
        'reserved_quantity' => 3,
        'status' => 'available',
    ]);

    $salesOrder = SalesOrder::factory()
        ->has(SalesOrderItem::factory()->state([
            'inventory_item_id' => $inventoryItem->id,
            'quantity' => 3,
        ]), 'items')
        ->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'shipped',
        ]);

    $this->patch(route('sales-orders.update-status', $salesOrder), [
        'status' => 'completed',
    ]);

    $inventoryItem->refresh();

    expect($inventoryItem->current_quantity)->toBe(7);
    expect($inventoryItem->reserved_quantity)->toBe(0);
});

test('releases reserved stock when transitioning shipped to cancelled', function () {
    $inventoryItem = InventoryItem::factory()->create([
        'tenant_id' => $this->tenant->id,
        'current_quantity' => 10,
        'reserved_quantity' => 3,
        'status' => 'available',
    ]);

    $salesOrder = SalesOrder::factory()
        ->has(SalesOrderItem::factory()->state([
            'inventory_item_id' => $inventoryItem->id,
            'quantity' => 3,
        ]), 'items')
        ->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'shipped',
        ]);

    $this->patch(route('sales-orders.update-status', $salesOrder), [
        'status' => 'cancelled',
    ]);

    $inventoryItem->refresh();

    // Stock not deducted, only reservation released
    expect($inventoryItem->current_quantity)->toBe(10);
    expect($inventoryItem->reserved_quantity)->toBe(0);
});

// ─────────────────────────────────────────────────────────
// Multi-tenant isolation
// ─────────────────────────────────────────────────────────

test('returns not found for sales orders belonging to another tenant', function () {
    $otherTenant = Tenant::factory()->create();
    $salesOrder = SalesOrder::factory()->create([
        'tenant_id' => $otherTenant->id,
        'status' => 'processing',
    ]);

    $response = $this->patch(route('sales-orders.update-status', $salesOrder), [
        'status' => 'shipped',
    ]);

    $response->assertNotFound();
});

// ─────────────────────────────────────────────────────────
// canTransitionTo model unit tests
// ─────────────────────────────────────────────────────────

test('canTransitionTo returns correct allowed transitions', function () {
    $order = new SalesOrder(['status' => 'processing']);
    expect($order->canTransitionTo('shipped'))->toBeTrue();
    expect($order->canTransitionTo('completed'))->toBeTrue();
    expect($order->canTransitionTo('cancelled'))->toBeTrue();
    expect($order->canTransitionTo('draft'))->toBeFalse();
    expect($order->canTransitionTo('confirmed'))->toBeFalse();
});

test('completed order has no allowed transitions', function () {
    $order = new SalesOrder(['status' => 'completed']);
    expect($order->allowedTransitions())->toBeEmpty();
    expect($order->canTransitionTo('draft'))->toBeFalse();
});

test('canBeEdited only returns true for draft', function () {
    $draftOrder = new SalesOrder(['status' => 'draft']);
    $confirmedOrder = new SalesOrder(['status' => 'confirmed', 'payment_status' => 'unpaid']);
    $processingOrder = new SalesOrder(['status' => 'processing']);
    $completedOrder = new SalesOrder(['status' => 'completed', 'payment_status' => 'unpaid']);

    expect($draftOrder->canBeEdited())->toBeTrue();
    expect($confirmedOrder->canBeEdited())->toBeFalse();
    expect($processingOrder->canBeEdited())->toBeFalse();
    expect($completedOrder->canBeEdited())->toBeFalse();
});
