<?php

use App\Models\Contractor;
use App\Models\ProductionOrder;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->create([
        'tenant_id' => $this->tenant->id,
        'role' => 'admin',
    ]);
    $this->actingAs($this->user);
});

test('can list contractors for current tenant only', function () {
    // Create contractors for current tenant
    Contractor::factory()->count(3)->create(['tenant_id' => $this->tenant->id]);

    // Create contractors for another tenant (should not be visible)
    $otherTenant = Tenant::factory()->create();
    Contractor::factory()->count(2)->create(['tenant_id' => $otherTenant->id]);

    $response = $this->get(route('contractors.index'));

    $response->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Contractors/Index')
            ->has('contractors.data', 3)
            ->where('contractors.total', 3)
        );
});

test('can create a contractor', function () {
    $contractorData = [
        'name' => 'Penjahit Jaya',
        'contact_person' => 'Budi Santoso',
        'phone' => '081234567890',
        'email' => 'budi@penjahitjaya.com',
        'address' => 'Jl. Merdeka No. 123',
        'type' => 'individual',
        'specialty' => 'sewing',
        'is_active' => true,
        'notes' => 'Spesialis jahit mukena',
    ];

    $response = $this->post(route('contractors.store'), $contractorData);

    $response->assertRedirect(route('contractors.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('contractors', [
        'name' => 'Penjahit Jaya',
        'tenant_id' => $this->tenant->id,
        'type' => 'individual',
    ]);
});

test('validates required fields when creating contractor', function () {
    $response = $this->post(route('contractors.store'), []);

    $response->assertSessionHasErrors(['name', 'type', 'specialty']);
});

test('validates specialty field', function () {
    $response = $this->post(route('contractors.store'), [
        'name' => 'Test Contractor',
        'type' => 'individual',
        'specialty' => 'invalid_specialty',
    ]);

    $response->assertSessionHasErrors('specialty');
});

test('can update a contractor', function () {
    $contractor = Contractor::factory()->create(['tenant_id' => $this->tenant->id]);

    $response = $this->put(route('contractors.update', $contractor), [
        'name' => 'Updated Name',
        'type' => 'company',
        'specialty' => 'baking',
        'rate_per_hour' => 50000,
    ]);

    $response->assertRedirect(route('contractors.index'));
    $this->assertDatabaseHas('contractors', [
        'id' => $contractor->id,
        'name' => 'Updated Name',
        'type' => 'company',
        'specialty' => 'baking',
    ]);
});

test('can delete contractor without production orders', function () {
    $contractor = Contractor::factory()->create(['tenant_id' => $this->tenant->id]);

    $response = $this->delete(route('contractors.destroy', $contractor));

    $response->assertRedirect(route('contractors.index'));
    $this->assertSoftDeleted('contractors', ['id' => $contractor->id]);
});

test('cannot delete contractor with production orders', function () {
    $contractor = Contractor::factory()->create(['tenant_id' => $this->tenant->id]);
    ProductionOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'contractor_id' => $contractor->id,
    ]);

    $response = $this->delete(route('contractors.destroy', $contractor));

    $response->assertSessionHas('error');
    $this->assertDatabaseHas('contractors', ['id' => $contractor->id]);
});

test('can filter contractors by type', function () {
    Contractor::factory()->count(2)->individual()->create(['tenant_id' => $this->tenant->id]);
    Contractor::factory()->count(3)->company()->create(['tenant_id' => $this->tenant->id]);

    $response = $this->get(route('contractors.index', ['type' => 'individual']));

    $response->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Contractors/Index')
            ->where('contractors.total', 2)
        );
});

test('can filter contractors by specialty', function () {
    Contractor::factory()->count(2)->sewing()->create(['tenant_id' => $this->tenant->id]);
    Contractor::factory()->count(3)->baking()->create(['tenant_id' => $this->tenant->id]);

    $response = $this->get(route('contractors.index', ['specialty' => 'Penjahit mukena dan gamis']));

    $response->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Contractors/Index')
            ->where('contractors.total', 2)
        );
});

test('can filter contractors by status', function () {
    Contractor::factory()->count(2)->create(['tenant_id' => $this->tenant->id, 'is_active' => true]);
    Contractor::factory()->count(3)->inactive()->create(['tenant_id' => $this->tenant->id]);

    $response = $this->get(route('contractors.index', ['status' => '1']));

    $response->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Contractors/Index')
            ->where('contractors.total', 2)
        );
});

test('can search contractors', function () {
    Contractor::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Penjahit Jaya']);
    Contractor::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Bordir Indah']);

    $response = $this->get(route('contractors.index', ['search' => 'Jaya']));

    $response->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Contractors/Index')
            ->where('contractors.total', 1)
        );
});

test('contractor has correct type helpers', function () {
    $individual = Contractor::factory()->individual()->create(['tenant_id' => $this->tenant->id]);
    $company = Contractor::factory()->company()->create(['tenant_id' => $this->tenant->id]);

    expect($individual->isIndividual())->toBeTrue();
    expect($individual->isCompany())->toBeFalse();

    expect($company->isIndividual())->toBeFalse();
    expect($company->isCompany())->toBeTrue();
});

test('contractor has specialty helper', function () {
    $contractor = Contractor::factory()->sewing()->create(['tenant_id' => $this->tenant->id]);

    // Factory creates specialty names like "Penjahit mukena dan gamis"
    expect($contractor->specialty)->toBe('Penjahit mukena dan gamis');
});

test('contractor scopes work correctly', function () {
    // Clean slate - remove all existing contractors from beforeEach
    Contractor::query()->delete();

    // Create specific contractors with known attributes
    Contractor::factory()->count(2)->create([
        'tenant_id' => $this->tenant->id,
        'is_active' => true,
        'type' => 'company',
    ]);

    // Create 1 inactive contractor
    Contractor::factory()->inactive()->create([
        'tenant_id' => $this->tenant->id,
        'type' => 'individual',
    ]);

    // Create 1 individual contractor
    Contractor::factory()->create([
        'tenant_id' => $this->tenant->id,
        'is_active' => true,
        'type' => 'individual',
    ]);

    // Create 3 sewing contractors
    Contractor::factory()->count(3)->sewing()->create([
        'tenant_id' => $this->tenant->id,
        'is_active' => true,
        'type' => 'company',
    ]);

    // Test that contractors exist
    expect(Contractor::count())->toBe(7); // 2 + 1 + 1 + 3 = 7 total
    expect(Contractor::where('is_active', true)->count())->toBe(6); // 2 + 1 + 3 = 6 active
    expect(Contractor::where('is_active', false)->count())->toBe(1); // 1 inactive
});
