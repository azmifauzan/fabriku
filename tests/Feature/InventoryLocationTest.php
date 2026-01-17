<?php

use App\Models\InventoryLocation;
use App\Models\Tenant;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->for($this->tenant)->create();

    $this->actingAs($this->user);
});

it('can list inventory locations', function () {
    $locations = InventoryLocation::factory()
        ->for($this->tenant)
        ->count(3)
        ->create();

    $response = $this->get('/inventory/locations');

    $response->assertSuccessful();
    $response->assertInertia(fn (AssertableInertia $page) => $page->component('Inventory/Locations/Index')
        ->has('locations.data', 3)
    );
});

it('can show inventory location details', function () {
    $location = InventoryLocation::factory()
        ->for($this->tenant)
        ->create();

    $response = $this->get("/inventory/locations/{$location->id}");

    $response->assertSuccessful();
    $response->assertInertia(fn (AssertableInertia $page) => $page->component('Inventory/Locations/Show')
        ->where('location.id', $location->id)
        ->where('location.name', $location->name)
    );
});

it('can create new inventory location', function () {
    $locationData = [
        'name' => 'Test Warehouse A1',
        'capacity' => 1000,
        'is_active' => true,
    ];

    $response = $this->post('/inventory/locations', $locationData);

    $response->assertRedirect();

    $this->assertDatabaseHas('inventory_locations', array_merge($locationData, [
        'tenant_id' => $this->tenant->id,
    ]));
});

it('validates required fields when creating location', function () {
    $response = $this->post('/inventory/locations', []);

    $response->assertSessionHasErrors(['name']);
});

it('validates unique name within tenant', function () {
    InventoryLocation::factory()
        ->for($this->tenant)
        ->create(['name' => 'Existing Location']);

    $response = $this->post('/inventory/locations', [
        'name' => 'Existing Location',
        'capacity' => 1000,
        'is_active' => true,
    ]);

    $response->assertSessionHasErrors(['name']);
});

it('can update inventory location', function () {
    $location = InventoryLocation::factory()
        ->for($this->tenant)
        ->create();

    $updateData = [
        'name' => 'Updated Location Name',
        'capacity' => 2000,
        'is_active' => false,
    ];

    $response = $this->put("/inventory/locations/{$location->id}", $updateData);

    $response->assertRedirect();

    $this->assertDatabaseHas('inventory_locations', array_merge($updateData, [
        'id' => $location->id,
        'tenant_id' => $this->tenant->id,
    ]));
});

it('can delete inventory location', function () {
    $location = InventoryLocation::factory()
        ->for($this->tenant)
        ->create();

    $response = $this->delete("/inventory/locations/{$location->id}");

    $response->assertRedirect();

    $this->assertSoftDeleted('inventory_locations', [
        'id' => $location->id,
    ]);
});

it('cannot access other tenant locations', function () {
    $otherTenant = Tenant::factory()->create();
    $location = InventoryLocation::factory()
        ->for($otherTenant)
        ->create();

    $response = $this->get("/inventory/locations/{$location->id}");

    $response->assertNotFound();
});

it('filters locations by status', function () {
    InventoryLocation::factory()
        ->for($this->tenant)
        ->create(['is_active' => true]);

    InventoryLocation::factory()
        ->for($this->tenant)
        ->create(['is_active' => false]);

    $response = $this->get('/inventory/locations?is_active=true');

    $response->assertSuccessful();
    $response->assertInertia(fn (AssertableInertia $page) => $page->component('Inventory/Locations/Index')
        ->has('locations.data', 1)
        ->where('locations.data.0.status', 'active')
    );
});

it('searches locations by name', function () {
    InventoryLocation::factory()
        ->for($this->tenant)
        ->create(['name' => 'Warehouse Alpha']);

    InventoryLocation::factory()
        ->for($this->tenant)
        ->create(['name' => 'Storage Beta']);

    $response = $this->get('/inventory/locations?search=Alpha');

    $response->assertSuccessful();
    $response->assertInertia(fn (AssertableInertia $page) => $page->component('Inventory/Locations/Index')
        ->has('locations.data', 1)
        ->where('locations.data.0.name', 'Warehouse Alpha')
    );
});

it('calculates available capacity correctly', function () {
    $location = InventoryLocation::factory()
        ->for($this->tenant)
        ->create(['capacity' => 1000]);

    // Create some inventory items in this location
    \App\Models\InventoryItem::factory()
        ->for($this->tenant)
        ->for($location, 'inventoryLocation')
        ->create(['current_quantity' => 200]);

    \App\Models\InventoryItem::factory()
        ->for($this->tenant)
        ->for($location, 'inventoryLocation')
        ->create(['current_quantity' => 300]);

    $location->refresh();

    expect($location->used_capacity)->toBe(500);
    expect($location->available_capacity)->toBe(500);
});

it('handles unlimited capacity locations', function () {
    $location = InventoryLocation::factory()
        ->for($this->tenant)
        ->unlimitedCapacity()
        ->create();

    expect($location->capacity)->toBeNull();
    expect($location->used_capacity)->toBe(0);
    expect($location->available_capacity)->toBe(PHP_INT_MAX);
});
