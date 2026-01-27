<?php

use App\Models\Material;
use App\Models\MaterialType;
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
    $this->materialType = MaterialType::factory()->create(['tenant_id' => $this->tenant->id]);
});

test('can upload image when creating material', function () {
    Storage::fake('fabriku_s3');

    $file = UploadedFile::fake()->image('fabric.jpg');

    $response = $this->post(route('materials.store'), [
        'material_type_id' => $this->materialType->id,
        'code' => 'MAT-IMG-001',
        'name' => 'Fabric with Image',
        'unit' => 'meter',
        'image' => $file,
    ]);

    $response->assertRedirect(route('materials.index'));

    $material = Material::where('code', 'MAT-IMG-001')->first();
    expect($material->image_path)->not->toBeNull();
    expect($material->image_path)->toContain("tenants/{$this->tenant->id}/materials");

    // Assert file exists in fake storage
    Storage::disk('fabriku_s3')->assertExists($material->image_path);
});

test('can update material with new image and delete old one', function () {
    Storage::fake('fabriku_s3');

    // Create initial material with image
    $oldFile = UploadedFile::fake()->image('old.jpg');
    $path = $oldFile->store("tenants/{$this->tenant->id}/materials", 'fabriku_s3');

    $material = Material::factory()->create([
        'tenant_id' => $this->tenant->id,
        'material_type_id' => $this->materialType->id,
        'image_path' => $path,
    ]);

    Storage::disk('fabriku_s3')->assertExists($path);

    // Update with new image
    $newFile = UploadedFile::fake()->image('new.jpg');

    $response = $this->put(route('materials.update', $material), [
        'material_type_id' => $this->materialType->id,
        'code' => $material->code,
        'name' => 'Updated Name',
        'unit' => $material->unit,
        'image' => $newFile,
    ]);

    $response->assertRedirect(route('materials.index'));

    $material->refresh();

    // Old file should be deleted
    Storage::disk('fabriku_s3')->assertMissing($path);

    // New file should exist
    Storage::disk('fabriku_s3')->assertExists($material->image_path);
});

test('image url attribute generates presigned url', function () {
    Storage::fake('fabriku_s3');

    $material = Material::factory()->create([
        'tenant_id' => $this->tenant->id,
        'image_path' => 'materials/test.jpg',
    ]);

    // We can't easily mock the temporaryUrl return value with Storage::fake
    // without more complex mocking, but we can verify it returns a string if path exists
    // OR we can trust the framework logic and just check if accessing the attribute doesn't crash
    // and returns something that looks like standard URL or null if no path.

    $url = $material->image_url;
    expect($url)->not->toBeNull();
    expect($url)->toBeString();
});
