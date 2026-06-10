<?php

use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\SalesOrder;
use App\Models\Service;
use App\Models\ServiceConsumable;
use App\Models\Staff;
use App\Models\Tenant;
use App\Models\User;

describe('Service Enhancements', function () {
    beforeEach(function () {
        $this->tenant = Tenant::factory()->create([
            'business_category' => 'service',
            'is_active' => true,
            'subscription_plan' => 'PRO',
            'subscription_expires_at' => now()->addMonth(),
        ]);

        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->service = Service::create([
            'tenant_id' => $this->tenant->id,
            'code' => 'SRV-OLI',
            'name' => 'Ganti Oli',
            'price' => 15000,
        ]);
    });

    describe('Laporan Layanan', function () {
        it('shows service report with correct aggregates', function () {
            $this->actingAs($this->user)
                ->post(route('sales-orders.quick-checkout.store'), [
                    'payment_method' => 'cash',
                    'items' => [
                        ['inventory_item_id' => null, 'service_id' => $this->service->id, 'quantity' => 3, 'unit_price' => 15000],
                    ],
                ])
                ->assertRedirect();

            $response = $this->actingAs($this->user)
                ->get(route('reports.service'))
                ->assertSuccessful();

            $services = $response->original->getData()['page']['props']['services'];
            expect($services)->toHaveCount(1);
            expect($services[0]['name'])->toBe('Ganti Oli');
            expect((int) $services[0]['total_sold'])->toBe(3);
            expect((float) $services[0]['total_revenue'])->toBe(45000.0);
        });

        it('exports service report to excel and pdf', function () {
            $this->actingAs($this->user)
                ->get(route('reports.service.export', ['format' => 'excel']))
                ->assertSuccessful();

            $this->actingAs($this->user)
                ->get(route('reports.service.export', ['format' => 'pdf']))
                ->assertSuccessful();
        });
    });

    describe('Staff Assignment per Layanan', function () {
        it('stores served_by on service line via quick checkout', function () {
            $staff = Staff::factory()->create(['tenant_id' => $this->tenant->id]);

            $this->actingAs($this->user)
                ->post(route('sales-orders.quick-checkout.store'), [
                    'payment_method' => 'cash',
                    'items' => [
                        ['inventory_item_id' => null, 'service_id' => $this->service->id, 'quantity' => 1, 'unit_price' => 15000, 'served_by' => $staff->id],
                    ],
                ])
                ->assertRedirect();

            $order = SalesOrder::where('tenant_id', $this->tenant->id)->latest('id')->first();
            expect($order->items()->first()->served_by)->toBe($staff->id);
        });

        it('rejects cross-tenant staff on served_by', function () {
            $otherTenant = Tenant::factory()->create();
            $foreignStaff = Staff::factory()->create(['tenant_id' => $otherTenant->id]);

            $this->actingAs($this->user)
                ->post(route('sales-orders.quick-checkout.store'), [
                    'payment_method' => 'cash',
                    'items' => [
                        ['inventory_item_id' => null, 'service_id' => $this->service->id, 'quantity' => 1, 'unit_price' => 15000, 'served_by' => $foreignStaff->id],
                    ],
                ])
                ->assertSessionHasErrors('items.0.served_by');
        });
    });

    describe('Consumable Auto-Deduct', function () {
        beforeEach(function () {
            $location = InventoryLocation::factory()->create(['tenant_id' => $this->tenant->id]);

            $this->oli = InventoryItem::factory()->create([
                'tenant_id' => $this->tenant->id,
                'location_id' => $location->id,
                'sku' => 'OLI-1L',
                'product_name' => 'Oli Mesin 1L',
                'current_quantity' => 10,
                'reserved_quantity' => 0,
                'status' => 'available',
            ]);

            ServiceConsumable::create([
                'tenant_id' => $this->tenant->id,
                'service_id' => $this->service->id,
                'inventory_item_id' => $this->oli->id,
                'quantity' => 1,
            ]);
        });

        it('deducts mapped consumables when service is sold via quick checkout', function () {
            $this->actingAs($this->user)
                ->post(route('sales-orders.quick-checkout.store'), [
                    'payment_method' => 'cash',
                    'items' => [
                        ['inventory_item_id' => null, 'service_id' => $this->service->id, 'quantity' => 2, 'unit_price' => 15000],
                    ],
                ])
                ->assertRedirect();

            $this->oli->refresh();
            expect($this->oli->current_quantity)->toBe(8); // 10 - (1 consumable x 2 layanan)
        });

        it('blocks checkout when consumable stock is insufficient', function () {
            $this->oli->update(['current_quantity' => 1]);

            $this->actingAs($this->user)
                ->post(route('sales-orders.quick-checkout.store'), [
                    'payment_method' => 'cash',
                    'items' => [
                        ['inventory_item_id' => null, 'service_id' => $this->service->id, 'quantity' => 2, 'unit_price' => 15000],
                    ],
                ])
                ->assertSessionHasErrors();

            expect(SalesOrder::where('tenant_id', $this->tenant->id)->count())->toBe(0);
            expect($this->oli->refresh()->current_quantity)->toBe(1);
        });

        it('manages consumable mapping through service update', function () {
            $location = InventoryLocation::factory()->create(['tenant_id' => $this->tenant->id]);
            $busi = InventoryItem::factory()->create([
                'tenant_id' => $this->tenant->id,
                'location_id' => $location->id,
                'current_quantity' => 5,
                'status' => 'available',
            ]);

            $this->actingAs($this->user)
                ->put(route('services.update', $this->service), [
                    'code' => 'SRV-OLI',
                    'name' => 'Ganti Oli',
                    'price' => 15000,
                    'is_active' => true,
                    'consumables' => [
                        ['inventory_item_id' => $busi->id, 'quantity' => 2],
                    ],
                ])
                ->assertRedirect(route('services.index'));

            $consumables = ServiceConsumable::where('service_id', $this->service->id)->get();
            expect($consumables)->toHaveCount(1);
            expect($consumables[0]->inventory_item_id)->toBe($busi->id);
            expect($consumables[0]->quantity)->toBe(2);
        });
    });
});
