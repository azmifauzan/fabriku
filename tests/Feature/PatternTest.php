<?php

use App\Models\CuttingOrder;
use App\Models\Material;
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

test('can list patterns', function () {
    Pattern::factory()->count(3)->create(['tenant_id' => $this->tenant->id]);

    $response = $this->get('/patterns');

    $response->assertSuccessful();
});

test('can create pattern', function () {
    $material = Material::factory()->create(['tenant_id' => $this->tenant->id]);

    $response = $this->post('/patterns', [
        'code' => 'MKN-001',
        'name' => 'Mukena Dewasa',
        'product_type' => 'mukena',
        'size' => 'all_size',
        'description' => 'Test mukena',
        'estimated_time' => 45,
        'standard_waste_percentage' => 5.0,
        'is_active' => true,
        'materials' => [
            [
                'material_id' => $material->id,
                'quantity_needed' => 2.5,
                'notes' => 'Untuk atasan dan bawahan',
            ],
        ],
    ]);

    $response->assertRedirect('/patterns');
    $this->assertDatabaseHas('patterns', [
        'tenant_id' => $this->tenant->id,
        'code' => 'MKN-001',
        'name' => 'Mukena Dewasa',
        'product_type' => 'mukena',
    ]);
    $this->assertDatabaseHas('pattern_materials', [
        'material_id' => $material->id,
        'quantity_needed' => 2.5,
    ]);
});

test('pattern code must be unique per tenant', function () {
    Pattern::factory()->create([
        'tenant_id' => $this->tenant->id,
        'code' => 'MKN-001',
    ]);

    $response = $this->post('/patterns', [
        'code' => 'MKN-001',
        'name' => 'Another Pattern',
        'product_type' => 'mukena',
        'is_active' => true,
    ]);

    $response->assertSessionHasErrors('code');
});

test('pattern code can be same across different tenants', function () {
    $otherTenant = Tenant::create([
        'name' => 'Other Tenant',
        'slug' => 'other-tenant',
        'is_active' => true,
    ]);

    Pattern::factory()->create([
        'tenant_id' => $otherTenant->id,
        'code' => 'MKN-001',
    ]);

    $response = $this->post('/patterns', [
        'code' => 'MKN-001',
        'name' => 'My Pattern',
        'product_type' => 'mukena',
        'is_active' => true,
    ]);

    $response->assertRedirect('/patterns');
    $this->assertDatabaseCount('patterns', 2);
});

test('can update pattern', function () {
    $pattern = Pattern::factory()->create(['tenant_id' => $this->tenant->id]);
    $material = Material::factory()->create(['tenant_id' => $this->tenant->id]);

    $response = $this->put("/patterns/{$pattern->id}", [
        'code' => $pattern->code,
        'name' => 'Updated Pattern',
        'product_type' => 'daster',
        'size' => 'L',
        'is_active' => false,
        'materials' => [
            [
                'material_id' => $material->id,
                'quantity_needed' => 2.0,
            ],
        ],
    ]);

    $response->assertRedirect('/patterns');
    $this->assertDatabaseHas('patterns', [
        'id' => $pattern->id,
        'name' => 'Updated Pattern',
        'product_type' => 'daster',
        'is_active' => false,
    ]);
});

test('can delete pattern without cutting orders', function () {
    $pattern = Pattern::factory()->create(['tenant_id' => $this->tenant->id]);

    $response = $this->delete("/patterns/{$pattern->id}");

    $response->assertRedirect('/patterns');
    $this->assertDatabaseMissing('patterns', ['id' => $pattern->id]);
});

test('cannot delete pattern with cutting orders', function () {
    $pattern = Pattern::factory()->create(['tenant_id' => $this->tenant->id]);
    CuttingOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'pattern_id' => $pattern->id,
    ]);

    $response = $this->delete("/patterns/{$pattern->id}");

    $response->assertSessionHasErrors();
    $this->assertDatabaseHas('patterns', ['id' => $pattern->id]);
});

test('can calculate material cost', function () {
    $pattern = Pattern::factory()->create(['tenant_id' => $this->tenant->id]);
    $material1 = Material::factory()->create(['tenant_id' => $this->tenant->id, 'standard_price' => 50000]);
    $material2 = Material::factory()->create(['tenant_id' => $this->tenant->id, 'standard_price' => 20000]);

    $pattern->materials()->attach([
        $material1->id => ['quantity_needed' => 2.5],
        $material2->id => ['quantity_needed' => 0.1],
    ]);

    $cost = $pattern->calculateMaterialCost();

    expect($cost)->toBe(127000.0); // (50000 * 2.5) + (20000 * 0.1)
});

test('users can only see patterns from their tenant', function () {
    $otherTenant = Tenant::create([
        'name' => 'Other Tenant',
        'slug' => 'other-tenant',
        'is_active' => true,
    ]);

    Pattern::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'My Pattern']);
    Pattern::factory()->create(['tenant_id' => $otherTenant->id, 'name' => 'Other Pattern']);

    $patterns = Pattern::all();

    expect($patterns)->toHaveCount(1);
    expect($patterns->first()->name)->toBe('My Pattern');
});
