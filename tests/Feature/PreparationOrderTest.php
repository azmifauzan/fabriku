<?php

use App\Models\Material;
use App\Models\Pattern;
use App\Models\PreparationOrder;
use App\Models\Tenant;
use App\Models\User;

beforeEach(function () {
    $this->tenant = Tenant::create([
        'name' => 'Test Tenant',
        'business_category' => 'garment',
        'is_active' => true,
        'subscription_plan' => 'trial',
        'subscription_expires_at' => now()->addDays(30),
    ]);

    $this->user = User::factory()->create([
        'tenant_id' => $this->tenant->id,
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $this->actingAs($this->user);
    session(['tenant_id' => $this->tenant->id]);
});

test('can list preparation orders', function () {
    PreparationOrder::factory()->count(3)->create(['tenant_id' => $this->tenant->id]);

    $response = $this->get('/preparation-orders');

    $response->assertSuccessful();
});

test('can create preparation order', function () {
    $pattern = Pattern::factory()->create(['tenant_id' => $this->tenant->id]);
    $material = Material::factory()->create(['tenant_id' => $this->tenant->id, 'stock_quantity' => 100]);

    $response = $this->post('/preparation-orders', [
        'pattern_id' => $pattern->id,
        'order_date' => now()->toDateString(),
        'prepared_by' => $this->user->id,
        'output_quantity' => 10,
        'output_unit' => 'pieces',
        'materials_used' => [
            [
                'material_id' => $material->id,
                'material_name' => $material->name,
                'quantity' => 5.0,
                'unit' => 'meter',
            ],
        ],
        'status' => 'draft',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('preparation_orders', [
        'tenant_id' => $this->tenant->id,
        'pattern_id' => $pattern->id,
        'output_quantity' => 10,
        'status' => 'draft',
    ]);

    // Verify material_usage is saved correctly
    $order = PreparationOrder::latest('id')->first();
    expect($order->material_usage)->not->toBeNull();
    expect($order->material_usage)->toBeArray();
    expect($order->material_usage[0]['material_name'])->toBe($material->name);
    expect((float) $order->material_usage[0]['quantity'])->toBe(5.0);
});

test('auto generates order number', function () {
    $order = PreparationOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'order_number' => null,
    ]);

    expect($order->order_number)->toMatch('/^PRP-\d{4}-\d{4}$/');
});

test('auto deducts material stock when completed', function () {
    $material = Material::factory()->create([
        'tenant_id' => $this->tenant->id,
        'stock_quantity' => 100,
    ]);

    $order = PreparationOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'material_usage' => [
            [
                'material_id' => $material->id,
                'quantity' => 10,
                'unit' => 'meter',
            ],
        ],
        'status' => 'in_progress',
    ]);

    // Update status to completed
    $order->update(['status' => 'completed']);

    $material->refresh();
    expect((float) $material->stock_quantity)->toBe(90.0);
});

test('users can only see their tenant preparation orders', function () {
    $otherTenant = Tenant::create([
        'name' => 'Other Tenant',
        'business_category' => 'food',
        'is_active' => true,
        'subscription_plan' => 'trial',
        'subscription_expires_at' => now()->addDays(30),
    ]);

    PreparationOrder::factory()->create(['tenant_id' => $this->tenant->id]);
    PreparationOrder::factory()->create(['tenant_id' => $otherTenant->id]);

    $orders = PreparationOrder::all();

    expect($orders)->toHaveCount(1);
    expect($orders->first()->tenant_id)->toBe($this->tenant->id);
});

test('can update preparation order', function () {
    $material = Material::factory()->create(['tenant_id' => $this->tenant->id, 'stock_quantity' => 100]);

    $order = PreparationOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'status' => 'draft',
    ]);

    $response = $this->put("/preparation-orders/{$order->id}", [
        'pattern_id' => $order->pattern_id,
        'order_date' => $order->preparation_date->toDateString(),
        'prepared_by' => $order->prepared_by,
        'output_quantity' => 20,
        'output_unit' => 'pieces',
        'materials_used' => [
            [
                'material_id' => $material->id,
                'material_name' => $material->name,
                'quantity' => 5.0,
                'unit' => 'meter',
            ],
        ],
        'status' => 'in_progress',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('preparation_orders', [
        'id' => $order->id,
        'output_quantity' => 20,
        'status' => 'in_progress',
    ]);

    // Verify material_usage is updated correctly
    $order->refresh();
    expect($order->material_usage)->not->toBeNull();
    expect($order->material_usage)->toBeArray();
    expect($order->material_usage[0]['material_name'])->toBe($material->name);
});

test('can delete preparation order', function () {
    $order = PreparationOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'status' => 'draft',
    ]);

    $response = $this->delete("/preparation-orders/{$order->id}");

    $response->assertRedirect();
    $this->assertSoftDeleted('preparation_orders', ['id' => $order->id]);
});
