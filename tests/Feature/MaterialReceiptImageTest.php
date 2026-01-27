<?php

use App\Models\Material;
use App\Models\MaterialReceipt;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->create([
        'tenant_id' => $this->tenant->id,
        'role' => 'admin',
    ]);
    $this->actingAs($this->user);
    $this->material = Material::factory()->create(['tenant_id' => $this->tenant->id]);
});

test('can upload image when restocking material', function () {
    Storage::fake('fabriku_s3');

    $file = UploadedFile::fake()->image('batch_proof.jpg');

    $response = $this->post(route('material-receipts.store'), [
        'material_id' => $this->material->id,
        'supplier_name' => 'Supplier A',
        'quantity' => 100,
        'unit_price' => 5000,
        'receipt_date' => now()->format('Y-m-d'),
        'batch_number' => 'BATCH-TEST-01',
        'image' => $file,
    ]);

    $response->assertSessionHas('success');

    $this->assertDatabaseHas('material_receipts', [
        'batch_number' => 'BATCH-TEST-01',
    ]);

    $receipt = MaterialReceipt::withoutGlobalScopes()->where('batch_number', 'BATCH-TEST-01')->first();
    expect($receipt)->not->toBeNull();
    expect($receipt->image_path)->not->toBeNull();
    expect($receipt->image_path)->toContain("tenants/{$this->tenant->id}/receipts");

    // Assert file exists in fake storage
    Storage::disk('fabriku_s3')->assertExists($receipt->image_path);
});

test('can retrieve image url from receipt', function () {
    Storage::fake('fabriku_s3');

    $receipt = MaterialReceipt::factory()->create([
        'tenant_id' => $this->tenant->id,
        'material_id' => $this->material->id,
        'image_path' => 'tenants/1/receipts/test.jpg',
    ]);

    expect($receipt->image_url)->not->toBeNull();
    expect($receipt->image_url)->toBeString();
});
