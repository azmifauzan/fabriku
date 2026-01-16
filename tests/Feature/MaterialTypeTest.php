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
