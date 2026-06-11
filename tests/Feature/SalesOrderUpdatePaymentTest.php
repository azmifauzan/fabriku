<?php

use App\Models\SalesOrder;
use App\Models\Tenant;
use App\Models\User;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->admin()->create(['tenant_id' => $this->tenant->id]);
    $this->actingAs($this->user);
});

test('marks order as unpaid when paid amount is zero', function () {
    $salesOrder = SalesOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'status' => 'processing',
        'total_amount' => 100000,
        'paid_amount' => 50000,
        'payment_status' => 'partial',
    ]);

    $response = $this->patch(route('sales-orders.update-payment', $salesOrder), [
        'paid_amount' => 0,
    ]);

    $response->assertRedirect(route('sales-orders.show', $salesOrder));

    $this->assertDatabaseHas('sales_orders', [
        'id' => $salesOrder->id,
        'paid_amount' => 0,
        'payment_status' => 'unpaid',
    ]);
});

test('marks order as partial when paid amount is between zero and total', function () {
    $salesOrder = SalesOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'status' => 'processing',
        'total_amount' => 100000,
        'paid_amount' => 0,
        'payment_status' => 'unpaid',
    ]);

    $response = $this->patch(route('sales-orders.update-payment', $salesOrder), [
        'paid_amount' => 40000,
    ]);

    $response->assertRedirect(route('sales-orders.show', $salesOrder));

    $this->assertDatabaseHas('sales_orders', [
        'id' => $salesOrder->id,
        'paid_amount' => 40000,
        'payment_status' => 'partial',
    ]);
});

test('marks order as paid when paid amount equals total', function () {
    $salesOrder = SalesOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'status' => 'processing',
        'total_amount' => 100000,
        'paid_amount' => 0,
        'payment_status' => 'unpaid',
    ]);

    $response = $this->patch(route('sales-orders.update-payment', $salesOrder), [
        'paid_amount' => 100000,
    ]);

    $response->assertRedirect(route('sales-orders.show', $salesOrder));

    $this->assertDatabaseHas('sales_orders', [
        'id' => $salesOrder->id,
        'paid_amount' => 100000,
        'payment_status' => 'paid',
    ]);
});

test('rejects paid amount greater than total amount', function () {
    $salesOrder = SalesOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'status' => 'processing',
        'total_amount' => 100000,
        'paid_amount' => 0,
        'payment_status' => 'unpaid',
    ]);

    $response = $this->patch(route('sales-orders.update-payment', $salesOrder), [
        'paid_amount' => 150000,
    ]);

    $response->assertSessionHasErrors('paid_amount');

    $this->assertDatabaseHas('sales_orders', [
        'id' => $salesOrder->id,
        'paid_amount' => 0,
        'payment_status' => 'unpaid',
    ]);
});

test('forbids updating payment for cancelled orders', function () {
    $salesOrder = SalesOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'status' => 'cancelled',
        'total_amount' => 100000,
        'paid_amount' => 0,
        'payment_status' => 'unpaid',
    ]);

    $response = $this->patch(route('sales-orders.update-payment', $salesOrder), [
        'paid_amount' => 100000,
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
        'paid_amount' => 100000,
    ]);

    $response->assertNotFound();
});
