<?php

use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\SalesOrder;
use App\Models\Service;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

describe('Service Tenant Workflow', function () {
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
    });

    it('successfully manages a service tenant journey end-to-end', function () {
        // Step 1: Dashboard redirects to quick checkout (POS-first category)
        $this->actingAs($this->user)
            ->get(route('dashboard'))
            ->assertRedirect(route('sales-orders.quick-checkout'));

        // Step 2: Stats view renders the service dashboard
        $this->actingAs($this->user)
            ->get(route('dashboard', ['view' => 'stats']))
            ->assertSuccessful();

        // Step 3: Create a service via the catalog CRUD
        $this->actingAs($this->user)
            ->post(route('services.store'), [
                'code' => 'SRV-CUKUR',
                'name' => 'Cukur Dewasa',
                'category' => 'Potong Rambut',
                'price' => 25000,
                'duration_minutes' => 30,
                'is_active' => true,
            ])
            ->assertRedirect(route('services.index'));

        $service = Service::where('code', 'SRV-CUKUR')->first();
        expect($service)->not->toBeNull();
        expect((float) $service->price)->toBe(25000.0);

        // Step 4: Duplicate code within the same tenant is rejected
        $this->actingAs($this->user)
            ->post(route('services.store'), [
                'code' => 'SRV-CUKUR',
                'name' => 'Duplikat',
                'price' => 10000,
            ])
            ->assertSessionHasErrors('code');

        // Step 5: Create a physical product (sparepart) with stock
        $location = InventoryLocation::factory()->create([
            'tenant_id' => $this->tenant->id,
            'code' => 'ETALASE',
        ]);

        $item = InventoryItem::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $location->id,
            'sku' => 'SVC-POMADE-01',
            'product_name' => 'Pomade',
            'current_quantity' => 10,
            'reserved_quantity' => 0,
            'selling_price' => 35000,
            'status' => 'available',
        ]);

        // Step 6: Quick checkout with a mixed cart (1 service + 1 product)
        $this->actingAs($this->user)
            ->post(route('sales-orders.quick-checkout.store'), [
                'payment_method' => 'cash',
                'customer_id' => null,
                'items' => [
                    ['inventory_item_id' => null, 'service_id' => $service->id, 'quantity' => 2, 'unit_price' => 25000],
                    ['inventory_item_id' => $item->id, 'service_id' => null, 'quantity' => 3, 'unit_price' => 35000],
                ],
            ])
            ->assertRedirect(route('sales-orders.index'));

        $order = SalesOrder::where('tenant_id', $this->tenant->id)->latest('id')->first();
        expect($order)->not->toBeNull();
        expect($order->status)->toBe('completed');
        expect($order->payment_status)->toBe('paid');
        expect((float) $order->total_amount)->toBe(155000.0); // 2*25000 + 3*35000

        // Product stock deducted, service line has no stock side effects
        $item->refresh();
        expect($item->current_quantity)->toBe(7);
        expect($item->reserved_quantity)->toBe(0);

        $serviceLine = $order->items()->whereNotNull('service_id')->first();
        expect($serviceLine)->not->toBeNull();
        expect($serviceLine->inventory_item_id)->toBeNull();
        expect($serviceLine->service_id)->toBe($service->id);

        // Step 7: Sales order detail page loads service relation
        $response = $this->actingAs($this->user)
            ->get(route('sales-orders.show', $order))
            ->assertSuccessful();

        $items = $response->original->getData()['page']['props']['salesOrder']['items'];
        $serviceItems = array_values(array_filter($items, fn ($i) => $i['service_id'] !== null));
        expect($serviceItems)->toHaveCount(1);
        expect($serviceItems[0]['service']['name'])->toBe('Cukur Dewasa');

        // Step 8: Sales report renders with service lines present
        $this->actingAs($this->user)
            ->get(route('reports.sales'))
            ->assertSuccessful();

        // Step 9: CSV export works with service lines
        $this->actingAs($this->user)
            ->get(route('sales-orders.export', $order))
            ->assertSuccessful();
    });

    it('allows the same service code on different tenants', function () {
        Service::create([
            'tenant_id' => $this->tenant->id,
            'code' => 'SRV-SAMA',
            'name' => 'Layanan Tenant 1',
            'price' => 10000,
        ]);

        $otherTenant = Tenant::factory()->create([
            'business_category' => 'service',
            'is_active' => true,
            'subscription_plan' => 'PRO',
            'subscription_expires_at' => now()->addMonth(),
        ]);
        $otherUser = User::factory()->create([
            'tenant_id' => $otherTenant->id,
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($otherUser)
            ->post(route('services.store'), [
                'code' => 'SRV-SAMA',
                'name' => 'Layanan Tenant 2',
                'price' => 20000,
            ])
            ->assertRedirect(route('services.index'))
            ->assertSessionHasNoErrors();
    });

    it('rejects cross-tenant service and inventory references in quick checkout', function () {
        $otherTenant = Tenant::factory()->create([
            'business_category' => 'service',
            'is_active' => true,
        ]);

        $foreignService = Service::withoutGlobalScopes()->create([
            'tenant_id' => $otherTenant->id,
            'code' => 'SRV-ASING',
            'name' => 'Layanan Tenant Lain',
            'price' => 50000,
        ]);

        $this->actingAs($this->user)
            ->post(route('sales-orders.quick-checkout.store'), [
                'payment_method' => 'cash',
                'items' => [
                    ['inventory_item_id' => null, 'service_id' => $foreignService->id, 'quantity' => 1, 'unit_price' => 50000],
                ],
            ])
            ->assertSessionHasErrors('items.0.service_id');

        expect(SalesOrder::withoutGlobalScopes()->where('tenant_id', $this->tenant->id)->count())->toBe(0);
    });

    it('rejects a line that references both a product and a service', function () {
        $service = Service::create([
            'tenant_id' => $this->tenant->id,
            'code' => 'SRV-X',
            'name' => 'Layanan X',
            'price' => 10000,
        ]);

        $location = InventoryLocation::factory()->create(['tenant_id' => $this->tenant->id]);
        $item = InventoryItem::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $location->id,
            'current_quantity' => 5,
            'status' => 'available',
        ]);

        $this->actingAs($this->user)
            ->post(route('sales-orders.quick-checkout.store'), [
                'payment_method' => 'cash',
                'items' => [
                    ['inventory_item_id' => $item->id, 'service_id' => $service->id, 'quantity' => 1, 'unit_price' => 10000],
                ],
            ])
            ->assertSessionHasErrors();

        expect(SalesOrder::withoutGlobalScopes()->where('tenant_id', $this->tenant->id)->count())->toBe(0);
    });

    it('does not expose a show route for services', function () {
        $service = Service::create([
            'tenant_id' => $this->tenant->id,
            'code' => 'SRV-SHOW',
            'name' => 'Layanan Show',
            'price' => 10000,
        ]);

        // GET /services/{id} tidak terdaftar; URI yang sama hanya melayani PUT/DELETE
        $this->actingAs($this->user)
            ->get("/services/{$service->id}")
            ->assertMethodNotAllowed();
    });

    it('blocks deleting a service already used in a sales order', function () {
        $service = Service::create([
            'tenant_id' => $this->tenant->id,
            'code' => 'SRV-DIPAKAI',
            'name' => 'Layanan Terpakai',
            'price' => 15000,
        ]);

        $this->actingAs($this->user)
            ->post(route('sales-orders.quick-checkout.store'), [
                'payment_method' => 'cash',
                'items' => [
                    ['inventory_item_id' => null, 'service_id' => $service->id, 'quantity' => 1, 'unit_price' => 15000],
                ],
            ])
            ->assertRedirect(route('sales-orders.index'));

        $this->actingAs($this->user)
            ->delete(route('services.destroy', $service))
            ->assertRedirect()
            ->assertSessionHas('error');

        expect(Service::find($service->id))->not->toBeNull();
    });

    it('allows deleting an unused service', function () {
        $service = Service::create([
            'tenant_id' => $this->tenant->id,
            'code' => 'SRV-BARU',
            'name' => 'Layanan Tak Terpakai',
            'price' => 15000,
        ]);

        $this->actingAs($this->user)
            ->delete(route('services.destroy', $service))
            ->assertRedirect(route('services.index'));

        expect(Service::find($service->id))->toBeNull();
    });

    it('keeps the service module disabled for non-service categories', function () {
        $otherCategories = ['garment', 'food', 'craft', 'cosmetic', 'retail', 'homemade'];

        foreach ($otherCategories as $category) {
            expect(config("business.categories.{$category}.rules.enable_service_module"))
                ->toBeFalse("Kategori {$category} harus punya enable_service_module = false eksplisit");
        }

        expect(config('business.categories.service.rules.enable_service_module'))->toBeTrue();
    });

    it('seeds default data when registering a service tenant', function () {
        Notification::fake();

        $this->post(route('register'), [
            'business_name' => 'Bengkel Test Sejahtera',
            'business_category' => 'service',
            'name' => 'Pemilik Bengkel',
            'email' => 'pemilik@bengkeltest.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertRedirect(route('verification.notice'));

        $tenant = Tenant::where('name', 'Bengkel Test Sejahtera')->first();
        expect($tenant)->not->toBeNull();

        expect(
            InventoryLocation::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count()
        )->toBeGreaterThan(0);

        expect(
            Customer::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->where('code', 'WALK-IN')
                ->exists()
        )->toBeTrue();
    });
});
