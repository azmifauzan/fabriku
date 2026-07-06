<?php

use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create([
        'name' => 'Toko Serba Ada',
        'business_category' => 'retail',
    ]);

    $this->user = User::factory()->create([
        'tenant_id' => $this->tenant->id,
        'email' => 'test@toko.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);

    $this->sourceRack = InventoryLocation::factory()->for($this->tenant)->create([
        'name' => 'Rak Asal',
        'capacity' => 300,
    ]);

    $this->rackA = InventoryLocation::factory()->for($this->tenant)->create([
        'name' => 'Rak A',
        'capacity' => 300,
    ]);

    $this->rackB = InventoryLocation::factory()->for($this->tenant)->create([
        'name' => 'Rak B',
        'capacity' => 300,
    ]);

    $this->item = InventoryItem::factory()->for($this->tenant)->create([
        'product_name' => 'Mukena Bali Putih',
        'location_id' => $this->sourceRack->id,
        'current_quantity' => 100,
        'reserved_quantity' => 0,
        'unit_cost' => 25000,
        'selling_price' => 45000,
    ]);
});

it('transfers stock across two racks from the item show page', function () {
    actingAs($this->user);

    $page = visit("/inventory/items/{$this->item->id}");

    $page->assertSee('Pindah/Split Stock')
        ->click('Pindah/Split Stock')
        ->assertSee('Lokasi Tujuan')
        ->select('[name="splits[0][location_id]"]', (string) $this->rackA->id)
        ->fill('[name="splits[0][quantity]"]', '40')
        ->click('Tambah Rak')
        ->select('[name="splits[1][location_id]"]', (string) $this->rackB->id)
        ->fill('[name="splits[1][quantity]"]', '30')
        ->fill('#reason', 'Test transfer split')
        ->click('button[type="submit"]')
        ->assertNoJavascriptErrors();

    // Source item quantity should be reduced by 70 to 30
    expect(InventoryItem::find($this->item->id)->current_quantity)->toBe(30);

    $this->assertDatabaseHas('inventory_items', [
        'product_name' => 'Mukena Bali Putih',
        'location_id' => $this->rackA->id,
        'current_quantity' => 40,
    ]);

    $this->assertDatabaseHas('inventory_items', [
        'product_name' => 'Mukena Bali Putih',
        'location_id' => $this->rackB->id,
        'current_quantity' => 30,
    ]);
});
