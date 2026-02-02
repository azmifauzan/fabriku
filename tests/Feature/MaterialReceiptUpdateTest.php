<?php

use App\Models\Material;
use App\Models\MaterialReceipt;
use App\Models\MaterialType;
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

    $this->materialType = MaterialType::factory()->create([
        'tenant_id' => $this->tenant->id,
    ]);
});

test('can update material receipt batch data', function () {
    $material = Material::factory()->create([
        'tenant_id' => $this->tenant->id,
        'material_type_id' => $this->materialType->id,
    ]);

    $receipt = MaterialReceipt::factory()->create([
        'tenant_id' => $this->tenant->id,
        'material_id' => $material->id,
        'supplier_name' => 'Old Supplier',
        'price_per_unit' => 10000,
        'quantity' => 100,
        'remaining_quantity' => 100,
    ]);

    $updateData = [
        'supplier_name' => 'New Supplier',
        'quantity' => 100,
        'price_per_unit' => 15000,
        'receipt_date' => '2026-01-15',
        'batch_number' => 'BATCH-NEW-001',
        'notes' => 'Updated notes',
    ];

    $response = $this->put(route('material-receipts.update', $receipt), $updateData);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('material_receipts', [
        'id' => $receipt->id,
        'supplier_name' => 'New Supplier',
        'price_per_unit' => 15000,
        'batch_number' => 'BATCH-NEW-001',
        'notes' => 'Updated notes',
        'total_cost' => 100 * 15000, // quantity * new price
    ]);
});

test('cannot update material receipt with invalid data', function () {
    $material = Material::factory()->create([
        'tenant_id' => $this->tenant->id,
        'material_type_id' => $this->materialType->id,
    ]);

    $receipt = MaterialReceipt::factory()->create([
        'tenant_id' => $this->tenant->id,
        'material_id' => $material->id,
        'quantity' => 100,
        'remaining_quantity' => 100,
    ]);

    $response = $this->put(route('material-receipts.update', $receipt), [
        'supplier_name' => '', // Required field
        'quantity' => 100,
        'price_per_unit' => -100, // Invalid negative price
        'receipt_date' => 'not-a-date', // Invalid date
    ]);

    $response->assertSessionHasErrors(['supplier_name', 'price_per_unit', 'receipt_date']);
});

test('updating receipt recalculates total cost', function () {
    $material = Material::factory()->create([
        'tenant_id' => $this->tenant->id,
        'material_type_id' => $this->materialType->id,
    ]);

    $receipt = MaterialReceipt::factory()->create([
        'tenant_id' => $this->tenant->id,
        'material_id' => $material->id,
        'quantity' => 50,
        'remaining_quantity' => 50,
        'price_per_unit' => 10000,
        'total_cost' => 500000,
    ]);

    $this->put(route('material-receipts.update', $receipt), [
        'supplier_name' => 'Supplier',
        'quantity' => 50,
        'price_per_unit' => 20000, // Double the price
        'receipt_date' => '2026-01-15',
    ]);

    $this->assertDatabaseHas('material_receipts', [
        'id' => $receipt->id,
        'total_cost' => 1000000, // 50 * 20000
    ]);
});

test('cannot reduce quantity below used amount', function () {
    $material = Material::factory()->create([
        'tenant_id' => $this->tenant->id,
        'material_type_id' => $this->materialType->id,
        'stock_quantity' => 70, // 100 - 30 used
    ]);

    // Receipt with 100 qty, 30 used (remaining 70)
    $receipt = MaterialReceipt::factory()->create([
        'tenant_id' => $this->tenant->id,
        'material_id' => $material->id,
        'quantity' => 100,
        'remaining_quantity' => 70, // 30 already used
        'price_per_unit' => 10000,
    ]);

    // Try to reduce quantity to 20 (below 30 used)
    $response = $this->put(route('material-receipts.update', $receipt), [
        'supplier_name' => 'Supplier',
        'quantity' => 20, // Invalid: less than 30 used
        'price_per_unit' => 10000,
        'receipt_date' => '2026-01-15',
    ]);

    $response->assertSessionHasErrors(['quantity']);
});

test('can increase quantity and updates material stock', function () {
    $material = Material::factory()->create([
        'tenant_id' => $this->tenant->id,
        'material_type_id' => $this->materialType->id,
        'stock_quantity' => 0, // Start with 0, receipt creation will add 100
    ]);

    $receipt = MaterialReceipt::factory()->create([
        'tenant_id' => $this->tenant->id,
        'material_id' => $material->id,
        'quantity' => 100,
        'remaining_quantity' => 100,
        'price_per_unit' => 10000,
    ]);

    // After receipt creation, stock should be 100 (0 + 100 from receipt observer)
    $material->refresh();
    expect((float) $material->stock_quantity)->toBe(100.0);

    $this->put(route('material-receipts.update', $receipt), [
        'supplier_name' => 'Supplier',
        'quantity' => 150, // Increase by 50
        'price_per_unit' => 10000,
        'receipt_date' => '2026-01-15',
    ]);

    // Check receipt updated
    $this->assertDatabaseHas('material_receipts', [
        'id' => $receipt->id,
        'quantity' => 150,
        'remaining_quantity' => 150, // Also increased by 50
    ]);

    // Check material stock updated: 100 + 50 = 150
    $material->refresh();
    expect((float) $material->stock_quantity)->toBe(150.0);
});

test('can decrease quantity to used amount and updates material stock', function () {
    $material = Material::factory()->create([
        'tenant_id' => $this->tenant->id,
        'material_type_id' => $this->materialType->id,
        'stock_quantity' => 0, // Start with 0
    ]);

    // Receipt with 100 qty, 30 used (remaining 70)
    $receipt = MaterialReceipt::factory()->create([
        'tenant_id' => $this->tenant->id,
        'material_id' => $material->id,
        'quantity' => 100,
        'remaining_quantity' => 70, // 30 already used
        'price_per_unit' => 10000,
    ]);

    // After receipt creation, stock is 100 (from observer), but we need to adjust for "used" scenario
    // Manually set stock to 70 to represent that 30 was used
    $material->update(['stock_quantity' => 70]);

    // Reduce to exactly 30 (the used amount) - should be allowed
    $this->put(route('material-receipts.update', $receipt), [
        'supplier_name' => 'Supplier',
        'quantity' => 30, // Reduce to used amount (reducing by 70)
        'price_per_unit' => 10000,
        'receipt_date' => '2026-01-15',
    ]);

    // Check receipt updated
    $this->assertDatabaseHas('material_receipts', [
        'id' => $receipt->id,
        'quantity' => 30,
        'remaining_quantity' => 0, // 70 - 70 (reduced by 70)
    ]);

    // Check material stock updated: 70 - 70 = 0
    $material->refresh();
    expect((float) $material->stock_quantity)->toBe(0.0);
});
