<?php

use App\Models\SalesOrder;
use App\Models\Tenant;
use App\Models\User;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->admin()->create(['tenant_id' => $this->tenant->id]);
    $this->actingAs($this->user);
});

test('adds a payment and marks order as partial', function () {
    $salesOrder = SalesOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'status' => 'processing',
        'total_amount' => 100000,
        'paid_amount' => 0,
        'payment_status' => 'unpaid',
    ]);

    $response = $this->patch(route('sales-orders.update-payment', $salesOrder), [
        'amount' => 40000,
        'method' => 'transfer',
        'paid_at' => now()->format('Y-m-d'),
        'note' => 'DP pertama',
    ]);

    $response->assertRedirect(route('sales-orders.show', $salesOrder));

    $this->assertDatabaseHas('payments', [
        'sales_order_id' => $salesOrder->id,
        'amount' => 40000,
        'method' => 'transfer',
        'note' => 'DP pertama',
    ]);

    $this->assertDatabaseHas('sales_orders', [
        'id' => $salesOrder->id,
        'paid_amount' => 40000,
        'payment_status' => 'partial',
    ]);
});

test('adds a payment and marks order as paid', function () {
    $salesOrder = SalesOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'status' => 'processing',
        'total_amount' => 100000,
        'paid_amount' => 0,
        'payment_status' => 'unpaid',
    ]);

    $response = $this->patch(route('sales-orders.update-payment', $salesOrder), [
        'amount' => 100000,
        'method' => 'cash',
        'paid_at' => now()->format('Y-m-d'),
    ]);

    $response->assertRedirect(route('sales-orders.show', $salesOrder));

    $this->assertDatabaseHas('sales_orders', [
        'id' => $salesOrder->id,
        'paid_amount' => 100000,
        'payment_status' => 'paid',
    ]);
});

test('rejects payment with amount less than or equal to zero', function () {
    $salesOrder = SalesOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'status' => 'processing',
        'total_amount' => 100000,
        'paid_amount' => 0,
        'payment_status' => 'unpaid',
    ]);

    $response = $this->patch(route('sales-orders.update-payment', $salesOrder), [
        'amount' => 0,
        'method' => 'cash',
        'paid_at' => now()->format('Y-m-d'),
    ]);

    $response->assertSessionHasErrors('amount');

    $this->assertDatabaseMissing('payments', [
        'sales_order_id' => $salesOrder->id,
    ]);
});

test('forbids adding payment for cancelled orders', function () {
    $salesOrder = SalesOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'status' => 'cancelled',
        'total_amount' => 100000,
        'paid_amount' => 0,
        'payment_status' => 'unpaid',
    ]);

    $response = $this->patch(route('sales-orders.update-payment', $salesOrder), [
        'amount' => 50000,
        'method' => 'cash',
        'paid_at' => now()->format('Y-m-d'),
    ]);

    $response->assertForbidden();
});

test('returns not found for sales orders belonging to another tenant', function () {
    $otherTenant = Tenant::factory()->create();
    $salesOrder = SalesOrder::factory()->create([
        'tenant_id' => $otherTenant->id,
        'status' => 'processing',
        'total_amount' => 100000,
        'paid_amount' => 0,
        'payment_status' => 'unpaid',
    ]);

    $response = $this->patch(route('sales-orders.update-payment', $salesOrder), [
        'amount' => 50000,
        'method' => 'cash',
        'paid_at' => now()->format('Y-m-d'),
    ]);

    $response->assertNotFound();
});
