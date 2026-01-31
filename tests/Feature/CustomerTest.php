<?php

use App\Models\Customer;
use App\Models\SalesOrder;
use App\Models\Tenant;
use App\Models\User;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->actingAs($this->user);
});

it('can list customers', function () {
    Customer::factory(5)->create(['tenant_id' => $this->tenant->id]);

    $response = $this->get(route('customers.index'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Customers/Index')
        ->has('customers.data', 5)
    );
});

it('can create a customer', function () {
    $customerData = [
        'code' => 'CUST-001',
        'name' => 'Toko Berkah',
        'phone' => '081234567890',
        'email' => 'toko@example.com',
        'address' => 'Jl. Merdeka No. 123',
        'city' => 'Jakarta',
        'province' => 'DKI Jakarta',
        'notes' => 'Customer loyal',
        'is_active' => true,
    ];

    $response = $this->post(route('customers.store'), $customerData);

    $response->assertRedirect();
    $this->assertDatabaseHas('customers', [
        'tenant_id' => $this->tenant->id,
        'code' => 'CUST-001',
        'name' => 'Toko Berkah',
    ]);
});

it('validates customer code uniqueness per tenant', function () {
    Customer::factory()->create([
        'tenant_id' => $this->tenant->id,
        'code' => 'CUST-001',
    ]);

    $response = $this->post(route('customers.store'), [
        'code' => 'CUST-001',
        'name' => 'Duplicate Customer',
    ]);

    $response->assertSessionHasErrors('code');
});

it('can update a customer', function () {
    $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);

    $updateData = [
        'code' => $customer->code,
        'name' => 'Updated Customer Name',
        'phone' => '08123456789',
        'email' => 'updated@example.com',
        'is_active' => true,
    ];

    $response = $this->put(route('customers.update', $customer), $updateData);

    $response->assertRedirect();
    $this->assertDatabaseHas('customers', [
        'id' => $customer->id,
        'name' => 'Updated Customer Name',
        'phone' => '08123456789',
    ]);
});

it('can view customer details', function () {
    $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);

    $response = $this->get(route('customers.show', $customer));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Customers/Show')
        ->has('customer')
        ->where('customer.id', $customer->id)
    );
});

it('can delete a customer without orders', function () {
    $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);

    $response = $this->delete(route('customers.destroy', $customer));

    $response->assertRedirect();
    $this->assertSoftDeleted('customers', ['id' => $customer->id]);
});

it('cannot delete a customer with sales orders', function () {
    $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);
    SalesOrder::factory()->create([
        'tenant_id' => $this->tenant->id,
        'customer_id' => $customer->id,
    ]);

    $response = $this->delete(route('customers.destroy', $customer));

    $response->assertSessionHasErrors();
    $this->assertDatabaseHas('customers', ['id' => $customer->id]);
});

it('can search customers', function () {
    Customer::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Toko Maju']);
    Customer::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Toko Jaya']);

    $response = $this->get(route('customers.index', ['search' => 'Maju']));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->has('customers.data', 1)
    );
});

it('can filter customers by status', function () {
    Customer::factory(3)->create(['tenant_id' => $this->tenant->id, 'is_active' => true]);
    Customer::factory(2)->create(['tenant_id' => $this->tenant->id, 'is_active' => false]);

    $response = $this->get(route('customers.index', ['status' => '1']));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->has('customers.data', 3)
    );
});

it('enforces tenant isolation for customers', function () {
    $otherTenant = Tenant::factory()->create();
    $otherCustomer = Customer::factory()->create(['tenant_id' => $otherTenant->id]);

    $response = $this->get(route('customers.show', $otherCustomer));

    $response->assertNotFound();
});
