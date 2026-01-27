<?php

use App\Models\Material;
use App\Models\MaterialType;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->materialType = MaterialType::factory()->create();
    Storage::fake('fabriku_s3');
});

it('can create material with initial stock and creates batch automatically', function () {
    $this->actingAs($this->user);

    $data = [
        'code' => 'TEST-001',
        'name' => 'Test Material',
        'material_type_id' => $this->materialType->id,
        'stock_quantity' => 100,
        'unit' => 'meter',
        'price_per_unit' => 5000,
        'min_stock' => 10,
        'supplier_name' => 'Test Supplier',
        'description' => 'Test Description',
    ];

    $response = $this->post('/materials', $data);

    $response->assertRedirect('/materials');
    $response->assertSessionHas('success');

    $material = Material::where('code', 'TEST-001')->first();
    expect($material)->not->toBeNull();
    expect((float) $material->stock_quantity)->toBe(100.0);

    // Check that a batch/receipt was created
    expect($material->receipts()->count())->toBe(1);

    $receipt = $material->receipts()->first();
    expect((float) $receipt->quantity)->toBe(100.0);
    expect((float) $receipt->remaining_quantity)->toBe(100.0);
    expect($receipt->batch_number)->toContain('BATCH-TEST-001');
    expect($receipt->status)->toBe('available');
});

it('can create material with image and batch gets the same image', function () {
    $this->actingAs($this->user);

    $image = UploadedFile::fake()->image('test-material.jpg');

    $data = [
        'code' => 'TEST-002',
        'name' => 'Test Material With Image',
        'material_type_id' => $this->materialType->id,
        'stock_quantity' => 50,
        'unit' => 'kg',
        'price_per_unit' => 10000,
        'image' => $image,
    ];

    $response = $this->post('/materials', $data);

    $response->assertRedirect('/materials');

    $material = Material::where('code', 'TEST-002')->first();
    expect($material)->not->toBeNull();
    expect($material->image_path)->not->toBeNull();

    // Check image was stored
    Storage::disk('fabriku_s3')->assertExists($material->image_path);

    // Check batch was created with the same image
    $receipt = $material->receipts()->first();
    expect($receipt)->not->toBeNull();
    expect($receipt->image_path)->toBe($material->image_path);
});

it('can create material without initial stock and no batch is created', function () {
    $this->actingAs($this->user);

    $data = [
        'code' => 'TEST-003',
        'name' => 'Test Material No Stock',
        'material_type_id' => $this->materialType->id,
        'stock_quantity' => 0,
        'unit' => 'pcs',
    ];

    $response = $this->post('/materials', $data);

    $response->assertRedirect('/materials');

    $material = Material::where('code', 'TEST-003')->first();
    expect($material)->not->toBeNull();
    expect((float) $material->stock_quantity)->toBe(0.0);

    // No batch should be created
    expect($material->receipts()->count())->toBe(0);
});
