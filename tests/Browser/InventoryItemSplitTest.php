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

    $this->rackA = InventoryLocation::factory()->for($this->tenant)->create([
        'name' => 'Rak A',
        'capacity' => 300,
    ]);

    $this->rackB = InventoryLocation::factory()->for($this->tenant)->create([
        'name' => 'Rak B',
        'capacity' => 300,
    ]);
});

it('splits stock across two racks when creating an inventory item', function () {
    actingAs($this->user);

    $page = visit('/inventory/items/create');

    $page->assertSee('Tambah Item Inventory')
        ->fill('name', 'Mukena Bali Putih')
        ->select('[name="locations[0][location_id]"]', (string) $this->rackA->id)
        ->fill('[name="locations[0][quantity]"]', '300')
        ->click('[data-testid="add-rack-button"]')
        ->select('[name="locations[1][location_id]"]', (string) $this->rackB->id)
        ->fill('[name="locations[1][quantity]"]', '100')
        ->fill('unit_cost', '25000')
        ->fill('selling_price', '45000')
        ->click('button[type="submit"]')
        ->assertNoJavascriptErrors()
        ->screenshot();

    expect(InventoryItem::where('product_name', 'Mukena Bali Putih')->count())->toBe(2);

    $this->assertDatabaseHas('inventory_items', [
        'product_name' => 'Mukena Bali Putih',
        'location_id' => $this->rackA->id,
        'current_quantity' => 300,
    ]);

    $this->assertDatabaseHas('inventory_items', [
        'product_name' => 'Mukena Bali Putih',
        'location_id' => $this->rackB->id,
        'current_quantity' => 100,
    ]);
});
