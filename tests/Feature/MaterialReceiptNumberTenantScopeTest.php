<?php

use App\Models\Material;
use App\Models\MaterialType;
use App\Models\User;

it('allows two tenants to independently receive their first receipt of the year without a unique violation', function () {
    $userA = User::factory()->create();
    $materialTypeA = MaterialType::factory()->create(['tenant_id' => $userA->tenant_id]);

    $userB = User::factory()->create();
    $materialTypeB = MaterialType::factory()->create(['tenant_id' => $userB->tenant_id]);

    $this->actingAs($userA)->post('/materials', [
        'code' => 'TEN-A-001',
        'name' => 'Material Tenant A',
        'material_type_id' => $materialTypeA->id,
        'stock_quantity' => 10,
        'unit' => 'pcs',
    ])->assertRedirect('/materials')->assertSessionHasNoErrors();

    $this->actingAs($userB)->post('/materials', [
        'code' => 'TEN-B-001',
        'name' => 'Material Tenant B',
        'material_type_id' => $materialTypeB->id,
        'stock_quantity' => 10,
        'unit' => 'pcs',
    ])->assertRedirect('/materials')->assertSessionHasNoErrors();

    $this->actingAs($userA);
    $receiptA = Material::where('code', 'TEN-A-001')->first()->receipts()->first();

    $this->actingAs($userB);
    $receiptB = Material::where('code', 'TEN-B-001')->first()->receipts()->first();

    expect($receiptA->receipt_number)->toBe($receiptB->receipt_number);
    expect($receiptA->tenant_id)->not->toBe($receiptB->tenant_id);
});
