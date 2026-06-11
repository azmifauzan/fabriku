<?php

use App\Models\Material;
use App\Models\MaterialReceipt;
use App\Models\PreparationOrder;
use App\Models\Tenant;
use App\Services\MaterialStockService;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->service = new MaterialStockService();
});

it('can check stock availability', function () {
    $material = Material::factory()->create([
        'tenant_id' => $this->tenant->id,
        'stock_quantity' => 100,
    ]);

    // Scenario 1: Sufficient stock
    $materialsUsed = [
        [
            'material_id' => $material->id,
            'quantity' => 30,
        ],
    ];
    $insufficient = $this->service->checkStockAvailability($materialsUsed);
    expect($insufficient)->toBeEmpty();

    // Scenario 2: Insufficient stock
    $materialsUsed = [
        [
            'material_id' => $material->id,
            'quantity' => 120,
        ],
    ];
    $insufficient = $this->service->checkStockAvailability($materialsUsed);
    expect($insufficient)->toHaveCount(1);
    expect($insufficient[0]['material_id'])->toBe($material->id);
    expect((float) $insufficient[0]['shortage'])->toBe(20.0);
});

it('checks stock availability for specific batches', function () {
    $material = Material::factory()->create([
        'tenant_id' => $this->tenant->id,
        'stock_quantity' => 100,
    ]);

    $receipt = MaterialReceipt::factory()->create([
        'tenant_id' => $this->tenant->id,
        'material_id' => $material->id,
        'quantity' => 40,
        'remaining_quantity' => 40,
        'status' => 'active',
    ]);

    // Sufficient batch stock
    $materialsUsed = [
        [
            'material_id' => $material->id,
            'quantity' => 30,
            'batch_id' => $receipt->id,
        ],
    ];
    $insufficient = $this->service->checkStockAvailability($materialsUsed);
    expect($insufficient)->toBeEmpty();

    // Insufficient batch stock
    $materialsUsed = [
        [
            'material_id' => $material->id,
            'quantity' => 50,
            'batch_id' => $receipt->id,
        ],
    ];
    $insufficient = $this->service->checkStockAvailability($materialsUsed);
    expect($insufficient)->toHaveCount(1);
    expect((float) $insufficient[0]['shortage'])->toBe(10.0);
});

it('deducts stock using FIFO logic when no batch is specified', function () {
    $material = Material::factory()->create([
        'tenant_id' => $this->tenant->id,
        'stock_quantity' => 20,
    ]);

    $receipt1 = MaterialReceipt::factory()->create([
        'tenant_id' => $this->tenant->id,
        'material_id' => $material->id,
        'quantity' => 30,
        'remaining_quantity' => 30,
        'receipt_date' => now()->subDays(2),
        'status' => 'active',
    ]);

    $receipt2 = MaterialReceipt::factory()->create([
        'tenant_id' => $this->tenant->id,
        'material_id' => $material->id,
        'quantity' => 50,
        'remaining_quantity' => 50,
        'receipt_date' => now()->subDay(),
        'status' => 'active',
    ]);

    $prepOrder = PreparationOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'status' => 'draft',
        'material_usage' => [
            [
                'material_id' => $material->id,
                'quantity' => 45,
            ],
        ],
    ]);

    $this->service->deductStock($prepOrder);

    // Assert receipt1 is exhausted
    $receipt1->refresh();
    expect((float) $receipt1->remaining_quantity)->toBe(0.0);
    expect($receipt1->status)->toBe('exhausted');

    // Assert receipt2 has remaining stock
    $receipt2->refresh();
    expect((float) $receipt2->remaining_quantity)->toBe(35.0); // 30 + 50 - 45 = 35
    expect($receipt2->status)->toBe('active');

    // Assert Material stock quantity is updated
    $material->refresh();
    expect((float) $material->stock_quantity)->toBe(55.0); // 100 - 45 = 55
});

it('deducts stock from a specific batch when specified', function () {
    $material = Material::factory()->create([
        'tenant_id' => $this->tenant->id,
        'stock_quantity' => 20,
    ]);

    $receipt1 = MaterialReceipt::factory()->create([
        'tenant_id' => $this->tenant->id,
        'material_id' => $material->id,
        'quantity' => 30,
        'remaining_quantity' => 30,
        'receipt_date' => now()->subDays(2),
        'status' => 'active',
    ]);

    $receipt2 = MaterialReceipt::factory()->create([
        'tenant_id' => $this->tenant->id,
        'material_id' => $material->id,
        'quantity' => 50,
        'remaining_quantity' => 50,
        'receipt_date' => now()->subDay(),
        'status' => 'active',
    ]);

    $prepOrder = PreparationOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'status' => 'draft',
        'material_usage' => [
            [
                'material_id' => $material->id,
                'quantity' => 20,
                'batch_id' => $receipt2->id,
            ],
        ],
    ]);

    $this->service->deductStock($prepOrder);

    // Assert receipt1 is untouched
    $receipt1->refresh();
    expect((float) $receipt1->remaining_quantity)->toBe(30.0);

    // Assert receipt2 is deducted
    $receipt2->refresh();
    expect((float) $receipt2->remaining_quantity)->toBe(30.0); // 50 - 20 = 30

    // Assert Material stock quantity is updated
    $material->refresh();
    expect((float) $material->stock_quantity)->toBe(80.0);
});
