<?php

use App\Models\Customer;
use App\Models\SalesOrder;
use App\Models\Tenant;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);
});

it('verifies allowed transitions from draft state', function () {
    $order = SalesOrder::factory()->draft()->create([
        'tenant_id' => $this->tenant->id,
        'customer_id' => $this->customer->id,
    ]);

    expect($order->canTransitionTo('confirmed'))->toBeTrue();
    expect($order->canTransitionTo('processing'))->toBeTrue();
    expect($order->canTransitionTo('cancelled'))->toBeTrue();

    expect($order->canTransitionTo('shipped'))->toBeFalse();
    expect($order->canTransitionTo('completed'))->toBeFalse();
});

it('verifies allowed transitions from confirmed state', function () {
    $order = SalesOrder::factory()->confirmed()->create([
        'tenant_id' => $this->tenant->id,
        'customer_id' => $this->customer->id,
    ]);

    expect($order->canTransitionTo('processing'))->toBeTrue();
    expect($order->canTransitionTo('shipped'))->toBeTrue();
    expect($order->canTransitionTo('completed'))->toBeTrue();
    expect($order->canTransitionTo('cancelled'))->toBeTrue();

    expect($order->canTransitionTo('draft'))->toBeFalse();
});

it('verifies allowed transitions from processing state', function () {
    $order = SalesOrder::factory()->processing()->create([
        'tenant_id' => $this->tenant->id,
        'customer_id' => $this->customer->id,
    ]);

    expect($order->canTransitionTo('shipped'))->toBeTrue();
    expect($order->canTransitionTo('completed'))->toBeTrue();
    expect($order->canTransitionTo('cancelled'))->toBeTrue();

    expect($order->canTransitionTo('draft'))->toBeFalse();
    expect($order->canTransitionTo('confirmed'))->toBeFalse();
});

it('verifies allowed transitions from shipped state', function () {
    $order = SalesOrder::factory()->shipped()->create([
        'tenant_id' => $this->tenant->id,
        'customer_id' => $this->customer->id,
    ]);

    expect($order->canTransitionTo('completed'))->toBeTrue();
    expect($order->canTransitionTo('cancelled'))->toBeTrue();

    expect($order->canTransitionTo('draft'))->toBeFalse();
    expect($order->canTransitionTo('confirmed'))->toBeFalse();
    expect($order->canTransitionTo('processing'))->toBeFalse();
});

it('verifies no transitions are allowed from completed state', function () {
    $order = SalesOrder::factory()->completed()->create([
        'tenant_id' => $this->tenant->id,
        'customer_id' => $this->customer->id,
    ]);

    expect($order->allowedTransitions())->toBeEmpty();
    expect($order->canTransitionTo('draft'))->toBeFalse();
    expect($order->canTransitionTo('cancelled'))->toBeFalse();
});

it('verifies no transitions are allowed from cancelled state', function () {
    $order = SalesOrder::factory()->cancelled()->create([
        'tenant_id' => $this->tenant->id,
        'customer_id' => $this->customer->id,
    ]);

    expect($order->allowedTransitions())->toBeEmpty();
    expect($order->canTransitionTo('draft'))->toBeFalse();
    expect($order->canTransitionTo('confirmed'))->toBeFalse();
});
