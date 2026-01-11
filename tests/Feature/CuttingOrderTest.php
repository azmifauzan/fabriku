<?php

use App\Models\CuttingOrder;
use App\Models\Pattern;
use App\Models\Tenant;
use App\Models\User;

beforeEach(function () {
    $this->tenant = Tenant::create([
        'name' => 'Test Tenant',
        'slug' => 'test-tenant',
        'is_active' => true,
    ]);

    $this->user = User::factory()->create([
        'tenant_id' => $this->tenant->id,
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $this->actingAs($this->user);

    // Set current tenant
    session(['tenant_id' => $this->tenant->id]);
});

test('can list cutting orders', function () {
    CuttingOrder::factory()->count(3)->create(['tenant_id' => $this->tenant->id]);

    $response = $this->get('/cutting-orders');

    $response->assertSuccessful();
});

test('can create cutting order with auto-generated order number', function () {
    $pattern = Pattern::factory()->create(['tenant_id' => $this->tenant->id]);

    $response = $this->post('/cutting-orders', [
        'pattern_id' => $pattern->id,
        'order_date' => '2026-01-15',
        'target_quantity' => 100,
    ]);

    $response->assertRedirect('/cutting-orders');
    $this->assertDatabaseHas('cutting_orders', [
        'tenant_id' => $this->tenant->id,
        'pattern_id' => $pattern->id,
        'target_quantity' => 100,
    ]);

    $order = CuttingOrder::first();
    expect($order->order_number)->toMatch('/^CO-\d{4}-\d{3}$/');
});

test('order number increments correctly', function () {
    $pattern = Pattern::factory()->create(['tenant_id' => $this->tenant->id]);

    $this->post('/cutting-orders', [
        'pattern_id' => $pattern->id,
        'order_date' => '2026-01-15',
        'target_quantity' => 100,
    ]);

    $this->post('/cutting-orders', [
        'pattern_id' => $pattern->id,
        'order_date' => '2026-01-16',
        'target_quantity' => 50,
    ]);

    $orders = CuttingOrder::orderBy('id')->get();
    expect($orders)->toHaveCount(2);

    // Extract numbers from order numbers
    preg_match('/(\d+)$/', $orders[0]->order_number, $matches1);
    preg_match('/(\d+)$/', $orders[1]->order_number, $matches2);

    expect((int) $matches2[1])->toBe((int) $matches1[1] + 1);
});

test('target date must be after or equal to order date', function () {
    $pattern = Pattern::factory()->create(['tenant_id' => $this->tenant->id]);

    $response = $this->post('/cutting-orders', [
        'pattern_id' => $pattern->id,
        'order_date' => '2026-01-15',
        'target_date' => '2026-01-10', // Before order date
        'target_quantity' => 100,
    ]);

    $response->assertSessionHasErrors('target_date');
});

test('can update draft order', function () {
    $order = CuttingOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'status' => 'draft',
    ]);

    $response = $this->put("/cutting-orders/{$order->id}", [
        'pattern_id' => $order->pattern_id,
        'order_date' => $order->order_date,
        'target_quantity' => 200,
    ]);

    $response->assertRedirect('/cutting-orders');
    $this->assertDatabaseHas('cutting_orders', [
        'id' => $order->id,
        'target_quantity' => 200,
    ]);
});

test('can update in progress order', function () {
    $order = CuttingOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'status' => 'in_progress',
    ]);

    $response = $this->put("/cutting-orders/{$order->id}", [
        'pattern_id' => $order->pattern_id,
        'order_date' => $order->order_date,
        'target_quantity' => 150,
    ]);

    $response->assertRedirect('/cutting-orders');
    $this->assertDatabaseHas('cutting_orders', [
        'id' => $order->id,
        'target_quantity' => 150,
    ]);
});

test('cannot update completed order', function () {
    $order = CuttingOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'status' => 'completed',
    ]);

    $response = $this->put("/cutting-orders/{$order->id}", [
        'pattern_id' => $order->pattern_id,
        'order_date' => $order->order_date,
        'target_quantity' => 300,
    ]);

    $response->assertSessionHasErrors();
});

test('can only delete draft orders', function () {
    $draftOrder = CuttingOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'status' => 'draft',
    ]);

    $response = $this->delete("/cutting-orders/{$draftOrder->id}");
    $response->assertRedirect('/cutting-orders');
    $this->assertDatabaseMissing('cutting_orders', ['id' => $draftOrder->id]);
});

test('cannot delete non-draft orders', function () {
    $completedOrder = CuttingOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'status' => 'completed',
    ]);

    $response = $this->delete("/cutting-orders/{$completedOrder->id}");

    $response->assertSessionHasErrors();
    $this->assertDatabaseHas('cutting_orders', ['id' => $completedOrder->id]);
});

test('status helper methods work correctly', function () {
    $order = CuttingOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'status' => 'draft',
    ]);

    expect($order->isDraft())->toBeTrue();
    expect($order->isInProgress())->toBeFalse();
    expect($order->isCompleted())->toBeFalse();
    expect($order->canBeEdited())->toBeTrue();
    expect($order->canBeDeleted())->toBeTrue();

    $order->update(['status' => 'in_progress']);
    expect($order->isInProgress())->toBeTrue();
    expect($order->canBeEdited())->toBeTrue();
    expect($order->canBeDeleted())->toBeFalse();

    $order->update(['status' => 'completed']);
    expect($order->isCompleted())->toBeTrue();
    expect($order->canBeEdited())->toBeFalse();
    expect($order->canBeDeleted())->toBeFalse();
});

test('users can only see orders from their tenant', function () {
    $otherTenant = Tenant::create([
        'name' => 'Other Tenant',
        'slug' => 'other-tenant',
        'is_active' => true,
    ]);

    CuttingOrder::factory()->create(['tenant_id' => $this->tenant->id]);
    CuttingOrder::factory()->create(['tenant_id' => $otherTenant->id]);

    $orders = CuttingOrder::all();

    expect($orders)->toHaveCount(1);
    expect($orders->first()->tenant_id)->toBe($this->tenant->id);
});
