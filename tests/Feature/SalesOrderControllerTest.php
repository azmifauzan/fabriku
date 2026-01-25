<?php

use App\Models\User;
use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\Tenant;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->actingAs($this->user);
});

test('create page renders', function () {
    // Create necessary data
    Customer::factory()->create(['tenant_id' => $this->tenant->id, 'is_active' => true]);
    InventoryItem::factory()->create(['tenant_id' => $this->tenant->id, 'status' => 'available']);

    $response = $this->get(route('sales-orders.create'));

    $response->assertStatus(200);
});
