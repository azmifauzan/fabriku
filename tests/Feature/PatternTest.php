<?php

use App\Models\Pattern;
use App\Models\PreparationOrder;
use App\Models\Tenant;
use App\Models\User;

beforeEach(function () {
    $this->tenant = Tenant::create([
        'name' => 'Test Tenant',
        'business_category' => 'garment',
        'subscription_plan' => 'trial',
        'subscription_expires_at' => now()->addDays(30),
    ]);

    $this->user = User::factory()->create([
        'tenant_id' => $this->tenant->id,
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $this->actingAs($this->user);

    // Set current tenant
    session(['tenant_id' => $this->tenant->id]);
});

test('can list patterns', function () {
    Pattern::factory()->count(3)->create(['tenant_id' => $this->tenant->id, 'category' => 'garment']);

    $response = $this->get('/patterns');

    $response->assertSuccessful();
});

test('can create pattern', function () {
    $response = $this->post('/patterns', [
        'code' => 'MKN-001',
        'name' => 'Mukena Dewasa',
        'category' => 'garment',
        'size' => 'all_size',
        'description' => 'Test mukena',
    ]);

    $response->assertRedirect('/patterns');
    $this->assertDatabaseHas('patterns', [
        'tenant_id' => $this->tenant->id,
        'code' => 'MKN-001',
        'name' => 'Mukena Dewasa',
        'category' => 'garment',
    ]);
});

test('pattern code must be unique per tenant', function () {
    Pattern::factory()->create([
        'tenant_id' => $this->tenant->id,
        'code' => 'MKN-001',
    ]);

    $response = $this->post('/patterns', [
        'code' => 'MKN-001',
        'name' => 'Another Pattern',
    ]);

    $response->assertSessionHasErrors('code');
});

test('pattern code can be same across different tenants', function () {
    $otherTenant = Tenant::create([
        'name' => 'Other Tenant',
        'business_category' => 'food',
        'subscription_plan' => 'trial',
        'subscription_expires_at' => now()->addDays(30),
    ]);

    Pattern::factory()->create([
        'tenant_id' => $otherTenant->id,
        'code' => 'MKN-001',
        'category' => 'food',
    ]);

    $response = $this->post('/patterns', [
        'code' => 'MKN-001',
        'name' => 'My Pattern',
        'category' => 'garment',
    ]);

    $response->assertRedirect('/patterns');
    $this->assertDatabaseCount('patterns', 2);
});

test('can update pattern', function () {
    $pattern = Pattern::factory()->create(['tenant_id' => $this->tenant->id, 'category' => 'garment']);

    $response = $this->put("/patterns/{$pattern->id}", [
        'code' => $pattern->code,
        'name' => 'Updated Pattern',
        'category' => 'garment',
        'size' => 'L',
    ]);

    $response->assertRedirect('/patterns');
    $this->assertDatabaseHas('patterns', [
        'id' => $pattern->id,
        'name' => 'Updated Pattern',
    ]);
});

test('can delete pattern without preparation orders', function () {
    $pattern = Pattern::factory()->create(['tenant_id' => $this->tenant->id, 'category' => 'garment']);

    $response = $this->delete("/patterns/{$pattern->id}");

    $response->assertRedirect('/patterns');
    // Soft delete - should still exist with deleted_at set
    $this->assertSoftDeleted('patterns', ['id' => $pattern->id]);
});

test('cannot delete pattern with preparation orders', function () {
    $pattern = Pattern::factory()->create(['tenant_id' => $this->tenant->id, 'category' => 'garment']);
    PreparationOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'pattern_id' => $pattern->id,
    ]);

    $response = $this->delete("/patterns/{$pattern->id}");

    $response->assertSessionHasErrors();
    $this->assertDatabaseHas('patterns', ['id' => $pattern->id]);
});

test('users can only see patterns from their tenant', function () {
    $otherTenant = Tenant::create([
        'name' => 'Other Tenant',
        'business_category' => 'craft',
        'subscription_plan' => 'trial',
        'subscription_expires_at' => now()->addDays(30),
    ]);

    Pattern::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'My Pattern', 'category' => 'garment']);
    Pattern::factory()->create(['tenant_id' => $otherTenant->id, 'name' => 'Other Pattern', 'category' => 'craft']);

    $patterns = Pattern::all();

    expect($patterns)->toHaveCount(1);
    expect($patterns->first()->name)->toBe('My Pattern');
});
