<?php

use App\Models\InventoryItemCategory;
use App\Models\InventoryLocation;
use App\Models\Tenant;
use App\Models\User;

uses()->group('inventory');

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->for($this->tenant)->admin()->create();
    $this->location = InventoryLocation::factory()->for($this->tenant)->create();
    $this->actingAs($this->user);
});

it('can list categories as json', function () {
    InventoryItemCategory::create([
        'tenant_id' => $this->tenant->id,
        'name' => 'Pakaian Wanita',
    ]);

    $response = $this->getJson('/inventory/categories');

    $response->assertSuccessful();
    $response->assertJsonFragment(['name' => 'Pakaian Wanita']);
});

it('can create a new category', function () {
    $response = $this->postJson('/inventory/categories', [
        'name' => 'Tas & Aksesoris',
        'description' => 'Kategori tas dan aksesoris',
    ]);

    $response->assertStatus(201);
    $response->assertJsonFragment(['name' => 'Tas & Aksesoris']);

    $this->assertDatabaseHas('inventory_item_categories', [
        'name' => 'Tas & Aksesoris',
        'tenant_id' => $this->tenant->id,
    ]);
});

it('requires name when creating category', function () {
    $response = $this->postJson('/inventory/categories', [
        'description' => 'Missing name',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['name']);
});

it('does not list inactive categories', function () {
    InventoryItemCategory::create([
        'tenant_id' => $this->tenant->id,
        'name' => 'Aktif',
        'is_active' => true,
    ]);
    InventoryItemCategory::create([
        'tenant_id' => $this->tenant->id,
        'name' => 'Tidak Aktif',
        'is_active' => false,
    ]);

    $response = $this->getJson('/inventory/categories');

    $response->assertSuccessful();
    $response->assertJsonFragment(['name' => 'Aktif']);
    $response->assertJsonMissing(['name' => 'Tidak Aktif']);
});

it('cannot see other tenant categories', function () {
    $otherTenant = Tenant::factory()->create();
    InventoryItemCategory::create([
        'tenant_id' => $otherTenant->id,
        'name' => 'Kategori Tenant Lain',
    ]);

    $response = $this->getJson('/inventory/categories');

    $response->assertSuccessful();
    $response->assertJsonMissing(['name' => 'Kategori Tenant Lain']);
});

it('can create inventory item with category', function () {
    $category = InventoryItemCategory::create([
        'tenant_id' => $this->tenant->id,
        'name' => 'Mukena',
    ]);

    $response = $this->post('/inventory/items', [
        'name' => 'Mukena Bali Putih',
        'location_id' => $this->location->id,
        'current_quantity' => 20,
        'unit_cost' => 50000,
        'selling_price' => 75000,
        'source_type' => 'opening_balance',
        'category_id' => $category->id,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('inventory_items', [
        'product_name' => 'Mukena Bali Putih',
        'category_id' => $category->id,
    ]);
});

it('can create manual entry without target quantity', function () {
    $response = $this->post('/inventory/items', [
        'name' => 'Kain Batik',
        'location_id' => $this->location->id,
        'current_quantity' => 10,
        'unit_cost' => 30000,
        'selling_price' => 45000,
        'source_type' => 'opening_balance',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('inventory_items', [
        'product_name' => 'Kain Batik',
        'target_quantity' => 0,
    ]);
});
