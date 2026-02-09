<?php

use App\Models\InventoryItem;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

beforeEach(function () {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

it('can generate QR code for an inventory item', function () {
    $item = InventoryItem::factory()->create([
        'tenant_id' => $this->user->tenant_id,
        'sku' => 'TEST-SKU-001',
    ]);

    $response = get(route('inventory.items.qrcode.generate', $item));

    $response->assertSuccessful();
    $response->assertHeader('Content-Type', 'image/svg+xml');
});

it('can display print QR code page', function () {
    $item = InventoryItem::factory()->create([
        'tenant_id' => $this->user->tenant_id,
        'sku' => 'TEST-SKU-002',
        'product_name' => 'Test Product',
    ]);

    $response = get(route('inventory.items.qrcode.print', $item));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Inventory/Items/PrintQrCode')
        ->has('item')
        ->where('item.id', $item->id)
        ->where('item.sku', 'TEST-SKU-002')
    );
});

it('can lookup item by SKU from scan', function () {
    $item = InventoryItem::factory()->create([
        'tenant_id' => $this->user->tenant_id,
        'sku' => 'TEST-SKU-003',
    ]);

    $response = post(route('inventory.items.scan-lookup'), [
        'sku' => 'TEST-SKU-003',
    ]);

    $response->assertSuccessful();
    $response->assertJson([
        'success' => true,
        'redirect_url' => route('inventory.items.show', $item),
    ]);
});

it('returns error when SKU not found during scan', function () {
    post(route('inventory.items.scan-lookup'), [
        'sku' => 'NON-EXISTENT-SKU',
    ])->assertNotFound()
        ->assertJson([
            'success' => false,
            'message' => 'Item dengan SKU tersebut tidak ditemukan.',
        ]);
});

it('requires SKU parameter for scan lookup', function () {
    post(route('inventory.items.scan-lookup'), [])
        ->assertSessionHasErrors(['sku']);
});

it('only shows items from user tenant in scan lookup', function () {
    // Create item from another tenant
    $otherTenantItem = InventoryItem::factory()->create([
        'tenant_id' => 999,
        'sku' => 'OTHER-TENANT-SKU',
    ]);

    // Create item from current user's tenant
    $myItem = InventoryItem::factory()->create([
        'tenant_id' => $this->user->tenant_id,
        'sku' => 'MY-TENANT-SKU',
    ]);

    // Try to scan other tenant's item
    post(route('inventory.items.scan-lookup'), [
        'sku' => 'OTHER-TENANT-SKU',
    ])->assertNotFound();

    // Can scan own tenant's item
    post(route('inventory.items.scan-lookup'), [
        'sku' => 'MY-TENANT-SKU',
    ])->assertSuccessful()
        ->assertJson([
            'success' => true,
            'redirect_url' => route('inventory.items.show', $myItem),
        ]);
});
