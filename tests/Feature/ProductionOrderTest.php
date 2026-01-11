<?php

use App\Models\Contractor;
use App\Models\CuttingOrder;
use App\Models\CuttingResult;
use App\Models\Material;
use App\Models\Pattern;
use App\Models\ProductionBatch;
use App\Models\ProductionOrder;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->create([
        'tenant_id' => $this->tenant->id,
        'role' => 'admin',
    ]);
    $this->actingAs($this->user);
});

function createCompletedCuttingResultForTenant(int $tenantId): CuttingResult
{
    $pattern = Pattern::factory()->create(['tenant_id' => $tenantId]);
    $cuttingOrder = CuttingOrder::factory()->create([
        'tenant_id' => $tenantId,
        'pattern_id' => $pattern->id,
        'status' => 'completed',
    ]);

    $material = Material::factory()->create(['tenant_id' => $tenantId]);

    return CuttingResult::factory()->create([
        'tenant_id' => $tenantId,
        'cutting_order_id' => $cuttingOrder->id,
        'material_id' => $material->id,
        'actual_quantity' => 120,
        'defect_quantity' => 5,
    ]);
}

test('can list production orders for current tenant only', function () {
    $cuttingResult = createCompletedCuttingResultForTenant($this->tenant->id);

    ProductionOrder::factory()->count(3)->create([
        'tenant_id' => $this->tenant->id,
        'cutting_result_id' => $cuttingResult->id,
    ]);

    // Create orders for another tenant (should not be visible)
    $otherTenant = Tenant::factory()->create();
    $otherCuttingResult = createCompletedCuttingResultForTenant($otherTenant->id);
    ProductionOrder::factory()->count(2)->create([
        'tenant_id' => $otherTenant->id,
        'cutting_result_id' => $otherCuttingResult->id,
    ]);

    $response = $this->get(route('production-orders.index'));

    $response->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('ProductionOrders/Index')
            ->where('orders.total', 3)
        );
});

test('can create a production order', function () {
    $cuttingResult = createCompletedCuttingResultForTenant($this->tenant->id);

    $orderData = [
        'cutting_result_id' => $cuttingResult->id,
        'type' => 'internal',
        'contractor_id' => null,
        'requested_date' => now()->format('Y-m-d'),
        'promised_date' => now()->addDays(7)->format('Y-m-d'),
        'quantity_requested' => 100,
        'priority' => 'normal',
        'labor_cost' => 500000,
    ];

    $response = $this->post(route('production-orders.store'), $orderData);

    $response->assertRedirect(route('production-orders.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('production_orders', [
        'cutting_result_id' => $cuttingResult->id,
        'quantity_requested' => 100,
        'status' => 'draft',
        'tenant_id' => $this->tenant->id,
    ]);
});

test('production order auto-generates order number', function () {
    $cuttingResult = createCompletedCuttingResultForTenant($this->tenant->id);

    $order = ProductionOrder::create([
        'tenant_id' => $this->tenant->id,
        'cutting_result_id' => $cuttingResult->id,
        'type' => 'internal',
        'contractor_id' => null,
        'requested_date' => now(),
        'promised_date' => now()->addDays(7),
        'quantity_requested' => 100,
        'status' => 'draft',
        'priority' => 'normal',
    ]);

    expect($order->order_number)->toMatch('/PO-\d{4}-\d{3}/');
});

test('can update production order when status allows', function () {
    $cuttingResult = createCompletedCuttingResultForTenant($this->tenant->id);

    $order = ProductionOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'cutting_result_id' => $cuttingResult->id,
        'status' => 'draft',
    ]);

    $response = $this->put(route('production-orders.update', $order), [
        'cutting_result_id' => $order->cutting_result_id,
        'type' => $order->type,
        'contractor_id' => $order->contractor_id,
        'requested_date' => $order->requested_date->format('Y-m-d'),
        'promised_date' => $order->promised_date?->format('Y-m-d'),
        'quantity_requested' => 200,
        'status' => 'pending',
        'priority' => 'high',
    ]);

    $response->assertRedirect(route('production-orders.index'));
    $this->assertDatabaseHas('production_orders', [
        'id' => $order->id,
        'quantity_requested' => 200,
        'status' => 'pending',
    ]);
});

test('cannot update production order when completed', function () {
    $cuttingResult = createCompletedCuttingResultForTenant($this->tenant->id);
    $order = ProductionOrder::factory()->completed()->create([
        'tenant_id' => $this->tenant->id,
        'cutting_result_id' => $cuttingResult->id,
    ]);

    $response = $this->put(route('production-orders.update', $order), [
        'cutting_result_id' => $order->cutting_result_id,
        'type' => $order->type,
        'contractor_id' => $order->contractor_id,
        'requested_date' => $order->requested_date->format('Y-m-d'),
        'promised_date' => $order->promised_date?->format('Y-m-d'),
        'quantity_requested' => 200,
        'status' => 'completed',
        'priority' => 'normal',
    ]);

    $response->assertSessionHas('error');
});

test('can delete production order when status is draft and no batches', function () {
    $cuttingResult = createCompletedCuttingResultForTenant($this->tenant->id);
    $order = ProductionOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'cutting_result_id' => $cuttingResult->id,
        'status' => 'draft',
    ]);

    $response = $this->delete(route('production-orders.destroy', $order));

    $response->assertRedirect(route('production-orders.index'));
    $this->assertDatabaseMissing('production_orders', ['id' => $order->id]);
});

test('cannot delete production order with batches', function () {
    $cuttingResult = createCompletedCuttingResultForTenant($this->tenant->id);
    $order = ProductionOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'cutting_result_id' => $cuttingResult->id,
        'status' => 'draft',
    ]);

    ProductionBatch::factory()->create([
        'tenant_id' => $this->tenant->id,
        'production_order_id' => $order->id,
    ]);

    $response = $this->delete(route('production-orders.destroy', $order));

    $response->assertSessionHas('error');
    $this->assertDatabaseHas('production_orders', ['id' => $order->id]);
});

test('can filter production orders by status', function () {
    $cuttingResult = createCompletedCuttingResultForTenant($this->tenant->id);

    ProductionOrder::factory()->count(2)->create([
        'tenant_id' => $this->tenant->id,
        'cutting_result_id' => $cuttingResult->id,
        'status' => 'draft',
    ]);
    ProductionOrder::factory()->count(3)->inProgress()->create([
        'tenant_id' => $this->tenant->id,
        'cutting_result_id' => $cuttingResult->id,
    ]);

    $response = $this->get(route('production-orders.index', ['status' => 'draft']));

    $response->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('ProductionOrders/Index')
            ->where('orders.total', 2)
        );
});

test('can filter production orders by contractor', function () {
    $contractor1 = Contractor::factory()->create(['tenant_id' => $this->tenant->id]);
    $contractor2 = Contractor::factory()->create(['tenant_id' => $this->tenant->id]);

    $cuttingResult = createCompletedCuttingResultForTenant($this->tenant->id);

    ProductionOrder::factory()->count(2)->create([
        'tenant_id' => $this->tenant->id,
        'cutting_result_id' => $cuttingResult->id,
        'contractor_id' => $contractor1->id,
    ]);
    ProductionOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'cutting_result_id' => $cuttingResult->id,
        'contractor_id' => $contractor2->id,
    ]);

    $response = $this->get(route('production-orders.index', ['contractor_id' => $contractor1->id]));

    $response->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('ProductionOrders/Index')
            ->where('orders.total', 2)
        );
});

test('production order status helpers work correctly', function () {
    $cuttingResult = createCompletedCuttingResultForTenant($this->tenant->id);

    $draft = ProductionOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'cutting_result_id' => $cuttingResult->id,
        'status' => 'draft',
    ]);
    $inProgress = ProductionOrder::factory()->inProgress()->create([
        'tenant_id' => $this->tenant->id,
        'cutting_result_id' => $cuttingResult->id,
    ]);
    $completed = ProductionOrder::factory()->completed()->create([
        'tenant_id' => $this->tenant->id,
        'cutting_result_id' => $cuttingResult->id,
    ]);

    expect($draft->isDraft())->toBeTrue();
    expect($draft->canBeEdited())->toBeTrue();
    expect($draft->canBeDeleted())->toBeTrue();

    expect($inProgress->isInProgress())->toBeTrue();
    expect($inProgress->canBeEdited())->toBeFalse();
    expect($inProgress->canBeDeleted())->toBeFalse();

    expect($completed->isCompleted())->toBeTrue();
    expect($completed->canBeEdited())->toBeFalse();
    expect($completed->canBeDeleted())->toBeFalse();
});

test('production order scopes work correctly', function () {
    $cuttingResult = createCompletedCuttingResultForTenant($this->tenant->id);

    ProductionOrder::factory()->count(2)->create([
        'tenant_id' => $this->tenant->id,
        'cutting_result_id' => $cuttingResult->id,
        'status' => 'draft',
    ]);
    ProductionOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'cutting_result_id' => $cuttingResult->id,
        'status' => 'pending',
    ]);
    ProductionOrder::factory()->inProgress()->create([
        'tenant_id' => $this->tenant->id,
        'cutting_result_id' => $cuttingResult->id,
    ]);

    expect(ProductionOrder::byStatus('draft')->count())->toBe(2);
    expect(ProductionOrder::pending()->count())->toBe(3); // draft + pending
    expect(ProductionOrder::inProgress()->count())->toBe(1);
});

test('production batch auto-generates batch number', function () {
    $cuttingResult = createCompletedCuttingResultForTenant($this->tenant->id);
    $order = ProductionOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'cutting_result_id' => $cuttingResult->id,
        'status' => 'sent',
    ]);

    $batch = ProductionBatch::create([
        'tenant_id' => $this->tenant->id,
        'production_order_id' => $order->id,
        'quantity_received' => 100,
        'quantity_good' => 95,
        'quantity_defect' => 3,
        'quantity_reject' => 2,
        'grade' => 'A',
        'production_date' => now(),
        'received_date' => now(),
        'received_by' => $this->user->id,
    ]);

    expect($batch->batch_number)->toMatch('/PB-\d{4}-\d{3}/');
});

test('production batch quality helpers work correctly', function () {
    $cuttingResult = createCompletedCuttingResultForTenant($this->tenant->id);
    $order = ProductionOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'cutting_result_id' => $cuttingResult->id,
    ]);

    $gradeA = ProductionBatch::factory()->gradeA()->create([
        'tenant_id' => $this->tenant->id,
        'production_order_id' => $order->id,
    ]);

    $gradeB = ProductionBatch::factory()->gradeB()->create([
        'tenant_id' => $this->tenant->id,
        'production_order_id' => $order->id,
    ]);

    $reject = ProductionBatch::factory()->reject()->create([
        'tenant_id' => $this->tenant->id,
        'production_order_id' => $order->id,
    ]);

    expect($gradeA->isGradeA())->toBeTrue();
    expect($gradeB->isGradeB())->toBeTrue();
    expect($reject->isReject())->toBeTrue();
});

test('can send external production order', function () {
    $cuttingResult = createCompletedCuttingResultForTenant($this->tenant->id);
    $contractor = Contractor::factory()->create(['tenant_id' => $this->tenant->id]);

    $order = ProductionOrder::factory()->external()->create([
        'tenant_id' => $this->tenant->id,
        'cutting_result_id' => $cuttingResult->id,
        'contractor_id' => $contractor->id,
        'status' => 'draft',
    ]);

    $response = $this->post(route('production-orders.send', $order));

    $response->assertSessionHas('success');
    $this->assertDatabaseHas('production_orders', [
        'id' => $order->id,
        'status' => 'sent',
    ]);
});

test('can receive a production batch and completes order when target reached', function () {
    $cuttingResult = createCompletedCuttingResultForTenant($this->tenant->id);
    $contractor = Contractor::factory()->create(['tenant_id' => $this->tenant->id]);

    $order = ProductionOrder::factory()->external()->create([
        'tenant_id' => $this->tenant->id,
        'cutting_result_id' => $cuttingResult->id,
        'contractor_id' => $contractor->id,
        'status' => 'sent',
        'quantity_requested' => 100,
    ]);

    $response = $this->post(route('production-orders.receive', $order), [
        'quantity_received' => 100,
        'quantity_good' => 95,
        'quantity_defect' => 3,
        'quantity_reject' => 2,
        'grade' => 'A',
        'production_date' => now()->subDay()->format('Y-m-d'),
        'received_date' => now()->format('Y-m-d'),
    ]);

    $response->assertRedirect(route('production-orders.show', $order))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('production_batches', [
        'tenant_id' => $this->tenant->id,
        'production_order_id' => $order->id,
        'quantity_received' => 100,
        'quantity_good' => 95,
        'quantity_defect' => 3,
        'quantity_reject' => 2,
        'grade' => 'A',
        'received_by' => $this->user->id,
    ]);

    $this->assertDatabaseHas('production_orders', [
        'id' => $order->id,
        'status' => 'completed',
        'quantity_produced' => 100,
        'quantity_good' => 95,
        'quantity_reject' => 2,
    ]);
});
