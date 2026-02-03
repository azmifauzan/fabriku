<?php

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
});

it('creates audit log when model is created', function () {
    $this->actingAs($this->user);

    $customer = Customer::create([
        'tenant_id' => $this->tenant->id,
        'code' => 'CUST-001',
        'name' => 'Test Customer',
        'phone' => '08123456789',
        'is_active' => true,
    ]);

    $auditLog = AuditLog::where('auditable_type', Customer::class)
        ->where('auditable_id', $customer->id)
        ->first();

    expect($auditLog)->not->toBeNull()
        ->and($auditLog->event)->toBe('created')
        ->and($auditLog->user_id)->toBe($this->user->id)
        ->and($auditLog->tenant_id)->toBe($this->tenant->id)
        ->and($auditLog->new_values)->toBeArray()
        ->and($auditLog->new_values['name'])->toBe('Test Customer');
});

it('creates audit log when model is updated', function () {
    $this->actingAs($this->user);

    $customer = Customer::create([
        'tenant_id' => $this->tenant->id,
        'code' => 'CUST-002',
        'name' => 'Original Name',
        'phone' => '08123456789',
        'is_active' => true,
    ]);

    // Clear initial creation log
    AuditLog::truncate();

    $customer->update(['name' => 'Updated Name']);

    $auditLog = AuditLog::where('auditable_type', Customer::class)
        ->where('auditable_id', $customer->id)
        ->where('event', 'updated')
        ->first();

    expect($auditLog)->not->toBeNull()
        ->and($auditLog->old_values['name'])->toBe('Original Name')
        ->and($auditLog->new_values['name'])->toBe('Updated Name');
});

it('creates audit log when model is deleted', function () {
    $this->actingAs($this->user);

    $customer = Customer::create([
        'tenant_id' => $this->tenant->id,
        'code' => 'CUST-003',
        'name' => 'To Be Deleted',
        'phone' => '08123456789',
        'is_active' => true,
    ]);

    $customerId = $customer->id;

    // Clear initial creation log
    AuditLog::truncate();

    $customer->delete();

    $auditLog = AuditLog::where('auditable_type', Customer::class)
        ->where('auditable_id', $customerId)
        ->where('event', 'deleted')
        ->first();

    expect($auditLog)->not->toBeNull()
        ->and($auditLog->old_values)->toBeArray()
        ->and($auditLog->old_values['name'])->toBe('To Be Deleted');
});

it('creates audit log when soft deleted model is restored', function () {
    $this->actingAs($this->user);

    $customer = Customer::create([
        'tenant_id' => $this->tenant->id,
        'code' => 'CUST-004',
        'name' => 'To Be Restored',
        'phone' => '08123456789',
        'is_active' => true,
    ]);

    $customer->delete();

    // Clear logs
    AuditLog::truncate();

    $customer->restore();

    $auditLog = AuditLog::where('auditable_type', Customer::class)
        ->where('auditable_id', $customer->id)
        ->where('event', 'restored')
        ->first();

    expect($auditLog)->not->toBeNull()
        ->and($auditLog->new_values)->toBeArray()
        ->and($auditLog->new_values['name'])->toBe('To Be Restored');
});

it('excludes password and timestamps from audit logs', function () {
    $this->actingAs($this->user);

    $customer = Customer::create([
        'tenant_id' => $this->tenant->id,
        'code' => 'CUST-005',
        'name' => 'Test Customer',
        'phone' => '08123456789',
        'is_active' => true,
    ]);

    $auditLog = AuditLog::where('auditable_type', Customer::class)
        ->where('auditable_id', $customer->id)
        ->first();

    expect($auditLog->new_values)->not->toHaveKey('created_at')
        ->and($auditLog->new_values)->not->toHaveKey('updated_at')
        ->and($auditLog->new_values)->not->toHaveKey('password');
});

it('records ip address and user agent in audit log', function () {
    $this->actingAs($this->user);

    $customer = Customer::create([
        'tenant_id' => $this->tenant->id,
        'code' => 'CUST-006',
        'name' => 'Test Customer',
        'phone' => '08123456789',
        'is_active' => true,
    ]);

    $auditLog = AuditLog::where('auditable_type', Customer::class)
        ->where('auditable_id', $customer->id)
        ->first();

    expect($auditLog->ip_address)->not->toBeNull()
        ->and($auditLog->url)->not->toBeNull();
});

it('can filter audit logs by event type', function () {
    $this->actingAs($this->user);

    $customer1 = Customer::create([
        'tenant_id' => $this->tenant->id,
        'code' => 'CUST-007',
        'name' => 'Customer 1',
        'phone' => '08123456789',
        'is_active' => true,
    ]);

    $customer2 = Customer::create([
        'tenant_id' => $this->tenant->id,
        'code' => 'CUST-008',
        'name' => 'Customer 2',
        'phone' => '08123456790',
        'is_active' => true,
    ]);

    $customer1->update(['name' => 'Updated Customer 1']);

    $createdLogs = AuditLog::event('created')->count();
    $updatedLogs = AuditLog::event('updated')->count();

    expect($createdLogs)->toBe(2)
        ->and($updatedLogs)->toBe(1);
});

it('can filter audit logs by model type', function () {
    $this->actingAs($this->user);

    $customer = Customer::create([
        'tenant_id' => $this->tenant->id,
        'code' => 'CUST-009',
        'name' => 'Test Customer',
        'phone' => '08123456789',
        'is_active' => true,
    ]);

    $customerLogs = AuditLog::forModel(Customer::class)->count();

    expect($customerLogs)->toBe(1);
});

it('can get changes attribute from audit log', function () {
    $this->actingAs($this->user);

    $customer = Customer::create([
        'tenant_id' => $this->tenant->id,
        'code' => 'CUST-010',
        'name' => 'Original',
        'phone' => '08123456789',
        'is_active' => true,
    ]);

    AuditLog::truncate();

    $customer->update(['name' => 'Updated', 'phone' => '08987654321']);

    $auditLog = AuditLog::where('event', 'updated')->first();

    $changes = $auditLog->changes;

    expect($changes)->toHaveKey('name')
        ->and($changes['name']['old'])->toBe('Original')
        ->and($changes['name']['new'])->toBe('Updated');
});

it('can access audit logs through model relationship', function () {
    $this->actingAs($this->user);

    $customer = Customer::create([
        'tenant_id' => $this->tenant->id,
        'code' => 'CUST-011',
        'name' => 'Test Customer',
        'phone' => '08123456789',
        'is_active' => true,
    ]);

    $customer->update(['name' => 'Updated Name']);

    expect($customer->auditLogs()->count())->toBe(2);
});
