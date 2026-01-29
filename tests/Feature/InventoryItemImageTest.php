<?php

use App\Models\InventoryItem;
use Illuminate\Support\Facades\Storage;

uses()->group('inventory');

beforeEach(function () {
    Storage::fake('fabriku_s3');
});

it('stores image_path in database', function () {
    $item = InventoryItem::factory()->create([
        'image_path' => 'tenants/1/inventory/test.jpg',
    ]);

    expect($item->image_path)->toBe('tenants/1/inventory/test.jpg');
    expect($item->fresh()->image_path)->toBe('tenants/1/inventory/test.jpg');
});

it('generates image_url accessor with presigned URL', function () {
    $item = InventoryItem::factory()->create([
        'image_path' => 'tenants/1/inventory/test.jpg',
    ]);

    Storage::disk('fabriku_s3')->put('tenants/1/inventory/test.jpg', 'test content');

    expect($item->image_url)->toBeString();
    expect($item->image_url)->toContain('https://');
    expect($item->image_url)->toContain('X-Amz-');
});

it('returns null image_url when no image_path exists', function () {
    $item = InventoryItem::factory()->create([
        'image_path' => null,
    ]);

    expect($item->image_url)->toBeNull();
});




