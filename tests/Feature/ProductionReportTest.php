<?php

use App\Models\Pattern;
use App\Models\PreparationOrder;
use App\Models\ProductionOrder;
use App\Models\Tenant;
use App\Models\User;

test('production report loads successfully', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'role' => 'admin',
    ]);

    $pattern = Pattern::factory()->create([
        'tenant_id' => $tenant->id,
    ]);

    $preparationOrder = PreparationOrder::factory()->create([
        'tenant_id' => $tenant->id,
        'pattern_id' => $pattern->id,
    ]);

    ProductionOrder::factory()->create([
        'tenant_id' => $tenant->id,
        'preparation_order_id' => $preparationOrder->id,
    ]);

    $this->actingAs($user);

    $response = $this->get(route('reports.production'));

    $response->assertSuccessful();

});
