<?php

use App\Models\Material;
use App\Models\MaterialType;
use App\Models\Tenant;
use App\Models\User;

test('material report loads successfully', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'role' => 'admin',
    ]);

    $materialType = MaterialType::factory()->create([
        'tenant_id' => $tenant->id,
    ]);

    Material::factory()->create([
        'tenant_id' => $tenant->id,
        'material_type_id' => $materialType->id,
    ]);

    $this->actingAs($user);

    $response = $this->get(route('reports.material'));

    $response->assertSuccessful();
});

test('material report can filter by material type', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'role' => 'admin',
    ]);

    $materialType1 = MaterialType::factory()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Type 1',
    ]);

    $materialType2 = MaterialType::factory()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Type 2',
    ]);

    Material::factory()->create([
        'tenant_id' => $tenant->id,
        'material_type_id' => $materialType1->id,
        'name' => 'Material 1',
    ]);

    Material::factory()->create([
        'tenant_id' => $tenant->id,
        'material_type_id' => $materialType2->id,
        'name' => 'Material 2',
    ]);

    $this->actingAs($user);

    $response = $this->get(route('reports.material', ['material_type' => $materialType1->id]));

    $response->assertSuccessful();
});
