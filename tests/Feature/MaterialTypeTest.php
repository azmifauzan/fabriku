<?php

use App\Models\MaterialType;
use App\Models\Tenant;
use App\Models\User;

it('requires unit when creating a material type', function () {
    $tenant = Tenant::factory()->active()->create();
    $user = User::factory()->forTenant($tenant->id)->create();

    $this->actingAs($user)
        ->post('/material-types', [
            'code' => 'MS',
            'name' => 'Mesin',
            'unit' => '',
            'description' => null,
            'sort_order' => 2,
            'is_active' => true,
        ])
        ->assertSessionHasErrors(['unit']);
});

it('creates a material type with unit', function () {
    $tenant = Tenant::factory()->active()->create();
    $user = User::factory()->forTenant($tenant->id)->create();

    $this->actingAs($user)
        ->post('/material-types', [
            'code' => 'MS',
            'name' => 'Mesin',
            'unit' => 'pcs',
            'description' => null,
            'sort_order' => 2,
            'is_active' => true,
        ])
        ->assertRedirect(route('material-types.index'));

    expect(MaterialType::query()->where('tenant_id', $tenant->id)->where('code', 'MS')->exists())->toBeTrue();
});

it('can delete material type when not used by any materials', function () {
    $tenant = Tenant::factory()->active()->create();
    $user = User::factory()->forTenant($tenant->id)->create();
    $materialType = MaterialType::factory()->create([
        'tenant_id' => $tenant->id,
    ]);

    $this->actingAs($user)
        ->delete(route('material-types.destroy', $materialType))
        ->assertRedirect(route('material-types.index'))
        ->assertSessionHas('success');

    expect(MaterialType::find($materialType->id))->toBeNull();
});

it('cannot delete material type when used by materials', function () {
    $tenant = Tenant::factory()->active()->create();
    $user = User::factory()->forTenant($tenant->id)->create();
    $materialType = MaterialType::factory()->create([
        'tenant_id' => $tenant->id,
    ]);

    // Create a material that uses this type
    \App\Models\Material::factory()->create([
        'tenant_id' => $tenant->id,
        'material_type_id' => $materialType->id,
    ]);

    $this->actingAs($user)
        ->delete(route('material-types.destroy', $materialType))
        ->assertRedirect()
        ->assertSessionHas('error');

    // Material type should still exist
    expect(MaterialType::find($materialType->id))->not->toBeNull();
});
