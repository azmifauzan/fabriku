<?php

use App\Models\Material;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->create([
        'tenant_id' => $this->tenant->id,
        'role' => 'admin',
    ]);
    $this->actingAs($this->user);

    // Create a material type for tests
    $this->materialType = \App\Models\MaterialType::factory()->create([
        'tenant_id' => $this->tenant->id,
    ]);
});

test('can list materials for current tenant only', function () {
    // Create materials for current tenant
    Material::factory()->count(3)->create(['tenant_id' => $this->tenant->id]);

    // Create materials for another tenant (should not be visible)
    $otherTenant = Tenant::factory()->create();
    Material::factory()->count(2)->create(['tenant_id' => $otherTenant->id]);

    $response = $this->get(route('materials.index'));

    $response->assertOk();

    // Inertia returns component data as props
    $materials = $response->viewData('page')['props']['materials'];
    expect($materials['total'])->toBe(3);
});

test('can search materials by name or code', function () {
    Material::factory()->create([
        'tenant_id' => $this->tenant->id,
        'name' => 'Kain Katun Premium',
        'code' => 'KA-001',
    ]);
    Material::factory()->create([
        'tenant_id' => $this->tenant->id,
        'name' => 'Benang Polyester',
        'code' => 'BN-001',
    ]);

    $response = $this->get(route('materials.index', ['search' => 'Katun']));

    $response->assertOk();
    $materials = $response->viewData('page')['props']['materials'];
    expect($materials['total'])->toBe(1);
    expect($materials['data'][0]['name'])->toContain('Katun');
});

test('can create new material', function () {
    $materialType = \App\Models\MaterialType::factory()->create(['tenant_id' => $this->tenant->id]);

    $materialData = [
        'material_type_id' => $materialType->id,
        'code' => 'MAT-001',
        'name' => 'Test Material',
        'unit' => 'meter',
        'price_per_unit' => 50000,
        'min_stock' => 50,
    ];

    $response = $this->post(route('materials.store'), $materialData);

    $response->assertRedirect(route('materials.index'));
    $this->assertDatabaseHas('materials', [
        'code' => 'MAT-001',
        'name' => 'Test Material',
        'tenant_id' => $this->tenant->id,
    ]);
});

test('code must be unique per tenant', function () {
    Material::factory()->create([
        'tenant_id' => $this->tenant->id,
        'code' => 'MAT-001',
    ]);

    $materialData = [
        'code' => 'MAT-001',
        'name' => 'Duplicate Material',
        'unit' => 'meter',
    ];

    $response = $this->post(route('materials.store'), $materialData);

    $response->assertSessionHasErrors('code');
});

test('code can be same across different tenants', function () {
    $otherTenant = Tenant::factory()->create();
    Material::factory()->create([
        'tenant_id' => $otherTenant->id,
        'code' => 'MAT-001',
    ]);

    $materialData = [
        'material_type_id' => $this->materialType->id,
        'code' => 'MAT-001',
        'name' => 'Same Code Different Tenant',
        'unit' => 'meter',
    ];

    $response = $this->post(route('materials.store'), $materialData);

    $response->assertRedirect(route('materials.index'));
    $this->assertDatabaseHas('materials', [
        'code' => 'MAT-001',
        'tenant_id' => $this->tenant->id,
    ]);
});

test('can update material', function () {
    $material = Material::factory()->create(['tenant_id' => $this->tenant->id]);

    $updateData = [
        'material_type_id' => $material->material_type_id,
        'code' => $material->code,
        'name' => 'Updated Material Name',
        'unit' => 'pcs',
        'standard_price' => 75000,
    ];

    $response = $this->put(route('materials.update', $material), $updateData);

    $response->assertRedirect(route('materials.index'));
    $this->assertDatabaseHas('materials', [
        'id' => $material->id,
        'name' => 'Updated Material Name',
    ]);
});

test('cannot update material from another tenant', function () {
    $otherTenant = Tenant::factory()->create();
    $material = Material::factory()->create(['tenant_id' => $otherTenant->id]);

    $updateData = [
        'code' => $material->code,
        'name' => 'Hacked Material',
        'unit' => 'meter',
    ];

    $response = $this->put(route('materials.update', $material), $updateData);

    $response->assertNotFound();
});

test('can delete material without receipts', function () {
    $material = Material::factory()->create(['tenant_id' => $this->tenant->id]);

    $response = $this->delete(route('materials.destroy', $material));

    $response->assertRedirect(route('materials.index'));
    $this->assertSoftDeleted('materials', ['id' => $material->id]);
});

test('cannot delete material with receipts', function () {
    $material = Material::factory()
        ->hasReceipts(1, ['tenant_id' => $this->tenant->id])
        ->create(['tenant_id' => $this->tenant->id]);

    $response = $this->delete(route('materials.destroy', $material));

    $response->assertRedirect();
    $response->assertSessionHas('error');
    $this->assertDatabaseHas('materials', ['id' => $material->id]);
});

test('material has low stock helper method', function () {
    $material = Material::factory()->create([
        'tenant_id' => $this->tenant->id,
        'stock_quantity' => 10,
        'reorder_point' => 50,
    ]);

    expect($material->isLowStock())->toBeTrue();

    $material->stock_quantity = 100;
    expect($material->isLowStock())->toBeFalse();
});

test('material automatically gets tenant_id from authenticated user', function () {
    $materialData = [
        'material_type_id' => $this->materialType->id,
        'code' => 'MAT-AUTO',
        'name' => 'Auto Tenant Material',
        'unit' => 'meter',
    ];

    $this->post(route('materials.store'), $materialData);

    $this->assertDatabaseHas('materials', [
        'code' => 'MAT-AUTO',
        'tenant_id' => $this->tenant->id,
    ]);
});
