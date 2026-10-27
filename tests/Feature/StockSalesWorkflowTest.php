<?php

use App\Jobs\NotifyStorefrontRequest;
use App\Models\BusinessSite;
use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\Lead;
use App\Models\Pattern;
use App\Models\PreparationOrder;
use App\Models\ProductionOrder;
use App\Models\SalesOrder;
use App\Models\Service;
use App\Models\SiteProduct;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create(['business_category' => 'food']);
    $this->owner = User::factory()->for($this->tenant)->create(['role' => 'admin']);
    $this->actingAs($this->owner);
});

it('preserves the exact default configuration and workflow of every category', function (string $category) {
    $tenant = Tenant::factory()->create(['business_category' => $category]);
    $user = User::factory()->for($tenant)->create(['role' => 'admin']);
    $expected = config("business.categories.{$category}");

    expect($tenant->usesStockSalesOnly())->toBeFalse()
        ->and($tenant->getCategoryConfig())->toBe($expected);

    $this->actingAs($user)->get('/dashboard?view=stats')
        ->assertOk()->assertInertia(fn (Assert $page) => $page
        ->where('tenant.business_category', $category)
        ->where('tenant.stock_sales_only', false)
        ->where('tenant.category_config.rules', $expected['rules'])
        ->where('tenant.category_config.mode', $expected['mode'] ?? 'full'));

    $tenant->update(['stock_sales_only' => false]);
    expect($tenant->getCategoryConfig())->toBe($expected);
})->with(['garment', 'food', 'craft', 'cosmetic', 'retail', 'homemade', 'service']);

it('changes only the selected tenant and restores its original workflow without changing data', function () {
    $other = Tenant::factory()->create(['business_category' => 'food']);
    $otherBefore = $other->refresh()->getAttributes();
    $item = InventoryItem::factory()->for($this->tenant)->create([
        'current_quantity' => 24, 'reserved_quantity' => 3, 'unit_cost' => 12000, 'selling_price' => 17000,
    ]);
    $order = SalesOrder::factory()->for($this->tenant)->create();
    $itemBefore = $item->refresh()->getAttributes();
    $orderBefore = $order->refresh()->getAttributes();

    foreach ([true, false] as $enabled) {
        $this->post('/settings/workflow', ['stock_sales_only' => $enabled, 'tenant_id' => $other->id])
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->tenant->refresh();
        expect($this->tenant->business_category)->toBe('food')
            ->and($this->tenant->usesStockSalesOnly())->toBe($enabled)
            ->and($item->refresh()->getAttributes())->toBe($itemBefore)
            ->and($order->refresh()->getAttributes())->toBe($orderBefore)
            ->and($other->refresh()->getAttributes())->toBe($otherBefore);
    }
    expect($this->tenant->getCategoryConfig())->toBe(config('business.categories.food'));
});

it('allows only the owner admin to save a validated boolean', function () {
    $this->post('/settings/workflow', ['stock_sales_only' => 'anything'])
        ->assertSessionHasErrors('stock_sales_only');
    $this->post('/settings/workflow', [])->assertSessionHasErrors('stock_sales_only');
    $staff = User::factory()->for($this->tenant)->create(['role' => 'staff']);
    $this->actingAs($staff)->post('/settings/workflow', ['stock_sales_only' => true])->assertForbidden();
    expect($this->tenant->refresh()->usesStockSalesOnly())->toBeFalse();
});

it('disables production routes only for an opted-in tenant and keeps food tracking', function () {
    $this->tenant->update(['stock_sales_only' => true]);
    foreach (['materials', 'material-receipts', 'material-types', 'patterns', 'preparation-orders', 'contractors', 'production-orders', 'simple-production', 'purchase-receipts', 'services', 'reports/material', 'reports/production', 'reports/purchase', 'reports/service'] as $path) {
        $this->get('/'.$path)->assertForbidden();
    }
    $this->post('/production-orders', [])->assertForbidden();
    $this->get('/inventory/items/create')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->where('tenant.business_category', 'food')
        ->where('tenant.category_label', 'Makanan & Kue')
        ->where('tenant.category_config.rules.track_expired_date', true)
        ->where('tenant.category_config.rules.track_inventory_cost', false)
        ->has('productionOrders', 0)->has('patterns', 0));
    $this->tenant->update(['stock_sales_only' => false]);
    $this->owner->unsetRelation('tenant');
    $this->get('/production-orders')->assertOk();
});

it('creates stock without modal input and preserves old cost and production links on edit', function () {
    $location = InventoryLocation::factory()->for($this->tenant)->create(['capacity' => null]);
    $pattern = Pattern::factory()->for($this->tenant)->create();
    $prep = PreparationOrder::factory()->for($this->tenant)->for($pattern)->create();
    $production = ProductionOrder::factory()->for($this->tenant)->for($prep)->create();
    $old = InventoryItem::factory()->for($this->tenant)->create([
        'location_id' => $location->id, 'production_order_id' => $production->id,
        'source_type' => 'production', 'target_quantity' => 30, 'unit_cost' => 4567,
        'current_quantity' => 20, 'reserved_quantity' => 0,
    ]);
    $this->tenant->update(['stock_sales_only' => true]);
    $payload = ['product_name' => 'Takoyaki', 'locations' => [['location_id' => $location->id, 'quantity' => 10]], 'selling_price' => 5000];
    $this->post('/inventory/items', $payload)->assertRedirect()->assertSessionHasNoErrors();
    $new = InventoryItem::where('product_name', 'Takoyaki')->firstOrFail();
    expect((float) $new->unit_cost)->toBe(0.0)->and($new->current_quantity)->toBe(10);
    $this->put('/inventory/items/'.$old->id, [
        'product_name' => 'Nama diperbarui', 'location_id' => $location->id,
        'current_quantity' => 20, 'unit_cost' => 99999, 'production_order_id' => null,
        'source_type' => 'opening_balance', 'target_quantity' => 0,
    ])->assertRedirect()->assertSessionHasNoErrors();
    $old->refresh();
    expect((float) $old->unit_cost)->toBe(4567.0)
        ->and($old->production_order_id)->toBe($production->id)
        ->and($old->source_type)->toBe('production')
        ->and($old->target_quantity)->toBe(30);
    $this->post('/inventory/items', [...$payload, 'production_order_id' => $production->id])
        ->assertSessionHasErrors('production_order_id');
});

it('omits cost calculations from dashboard and inventory reports including exports', function () {
    InventoryItem::factory()->for($this->tenant)->create([
        'current_quantity' => 10, 'unit_cost' => 4321, 'reserved_quantity' => 0,
    ]);
    $this->tenant->update(['stock_sales_only' => true]);
    $this->get('/dashboard?view=stats')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('RetailDashboard')->where('stats.inventory_value', null)->has('recentPurchases', 0));
    $response = $this->get('/reports/inventory')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->where('summary.total_value', null)->where('items.0.unit_price', null)
        ->where('items.0.total_value', null)->where('summary.total_quantity', 10));
    $data = $response->original->getData()['page']['props'];
    foreach (['exports.inventory-report', 'pdf.inventory-report'] as $view) {
        $html = view($view, $data)->render();
        expect($html)->not->toContain('Unit Price', 'Total Value', 'Total Nilai', '4.321');
    }
    foreach (['excel', 'pdf'] as $format) {
        $this->get('/reports/inventory/export?format='.$format)->assertOk();
    }
});

it('sells only available stock, rejects service sales, and retains the selling price', function () {
    $this->tenant->update(['stock_sales_only' => true]);
    $item = InventoryItem::factory()->for($this->tenant)->create([
        'current_quantity' => 10, 'reserved_quantity' => 2, 'unit_cost' => 3210,
        'selling_price' => 5000, 'status' => 'available', 'expired_date' => null,
    ]);
    $payload = ['payment_method' => 'cash', 'items' => [['inventory_item_id' => $item->id, 'quantity' => 3, 'unit_price' => 5000]]];
    $this->post('/sales-orders/quick-checkout', $payload)->assertRedirect()->assertSessionHasNoErrors();
    expect($item->refresh()->current_quantity)->toBe(7)
        ->and((float) $item->unit_cost)->toBe(3210.0)
        ->and((float) $item->selling_price)->toBe(5000.0);
    $this->post('/sales-orders/quick-checkout', [...$payload, 'items' => [['inventory_item_id' => $item->id, 'quantity' => 99, 'unit_price' => 5000]]])
        ->assertStatus(422);
    expect($item->refresh()->current_quantity)->toBe(7)
        ->and(SalesOrder::count())->toBe(1);
    $service = Service::create(['tenant_id' => $this->tenant->id, 'code' => 'SVC-TEST', 'name' => 'Layanan', 'price' => 20000]);
    $this->get('/sales-orders/quick-checkout')->assertOk()->assertInertia(fn (Assert $page) => $page->has('services', 0));
    $serviceLine = ['service_id' => $service->id, 'quantity' => 1, 'unit_price' => 20000];
    $this->post('/sales-orders/quick-checkout', [...$payload, 'items' => [$serviceLine]])->assertSessionHasErrors('items.0.service_id');
    $customer = Customer::factory()->for($this->tenant)->create();
    $this->post('/sales-orders', [
        'customer_id' => $customer->id, 'order_date' => now()->toDateString(), 'channel' => 'offline',
        'payment_method' => 'cash', 'items' => [$serviceLine],
    ])->assertSessionHasErrors('items.0.service_id');
});

it('adds the setting to a populated database without changing existing categories or operational rows', function () {
    foreach (['garment', 'food', 'craft', 'cosmetic', 'retail', 'homemade', 'service'] as $category) {
        Tenant::factory()->create(['business_category' => $category]);
    }
    InventoryItem::factory()->for($this->tenant)->create(['current_quantity' => 15, 'unit_cost' => 7890]);
    SalesOrder::factory()->for($this->tenant)->create();
    $tables = ['tenants', 'users', 'inventory_items', 'sales_orders'];
    $snapshot = [];
    foreach ($tables as $table) {
        $snapshot[$table] = DB::table($table)->orderBy('id')->get()->map(function ($row) {
            $data = (array) $row;
            unset($data['stock_sales_only']);

            return $data;
        })->all();
    }
    $migration = require database_path('migrations/2026_10_27_000001_add_stock_sales_only_to_tenants_table.php');
    $migration->down();
    $migration->up();
    foreach ($tables as $table) {
        $columns = array_keys($snapshot[$table][0]);
        $after = DB::table($table)->select($columns)->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();
        expect($after)->toBe($snapshot[$table]);
    }
    expect(Tenant::where('stock_sales_only', true)->count())->toBe(0)
        ->and(Tenant::where('stock_sales_only', false)->count())->toBe(count($snapshot['tenants']));
});

it('restores the original cost valuation after turning the mode off', function () {
    InventoryItem::factory()->for($this->tenant)->create([
        'current_quantity' => 10, 'reserved_quantity' => 0, 'unit_cost' => 4321,
    ]);
    foreach ([false, true, false] as $enabled) {
        $this->post('/settings/workflow', ['stock_sales_only' => $enabled])->assertRedirect();
        $this->owner->unsetRelation('tenant');
        $this->get('/reports/inventory')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('items.0.unit_price', $enabled ? null : 4321)
            ->where('summary.total_value', $enabled ? null : 43210));
    }
});

it('keeps old service leads intact and prevents converting them into sales while stock-only mode is active', function () {
    $site = BusinessSite::factory()->create(['tenant_id' => $this->tenant->id]);
    $service = Service::create(['tenant_id' => $this->tenant->id, 'code' => 'SVC-LEAD', 'name' => 'Layanan lama', 'price' => 20000]);
    $lead = Lead::create([
        'tenant_id' => $this->tenant->id, 'business_site_id' => $site->id,
        'service_id' => $service->id, 'source' => 'website', 'name' => 'Pelanggan lama',
        'phone' => '081234567890', 'status' => 'new', 'consent_at' => now(), 'consent_text' => 'Setuju',
    ]);
    $before = $lead->refresh()->getAttributes();
    $this->post('/settings/workflow', ['stock_sales_only' => true])->assertRedirect();
    $this->owner->unsetRelation('tenant');
    $this->post('/website/leads/'.$lead->id.'/convert')->assertForbidden();
    expect(SalesOrder::count())->toBe(0)
        ->and($lead->refresh()->getAttributes())->toBe($before);
    $this->post('/settings/workflow', ['stock_sales_only' => false])->assertRedirect();
    $this->owner->unsetRelation('tenant');
    $this->post('/website/leads/'.$lead->id.'/convert')->assertRedirect()->assertSessionHasNoErrors();
    expect($lead->refresh()->status)->toBe('converted')->and(SalesOrder::count())->toBe(1);
});

it('keeps the public storefront checkout working with the same price and stock rules', function (bool $enabled) {
    $this->tenant->update(['stock_sales_only' => $enabled]);
    BusinessSite::factory()->create([
        'tenant_id' => $this->tenant->id, 'slug' => 'stock-mode-checkout', 'mode' => 'produk', 'status' => 'published',
    ]);
    $item = InventoryItem::factory()->for($this->tenant)->create([
        'selling_price' => 5000, 'unit_cost' => 1234, 'current_quantity' => 10,
        'reserved_quantity' => 0, 'status' => 'available', 'expired_date' => null,
    ]);
    $product = SiteProduct::factory()->create([
        'tenant_id' => $this->tenant->id, 'inventory_item_id' => $item->id,
    ]);
    Queue::fake([NotifyStorefrontRequest::class]);
    auth()->logout();
    $base = 'http://stock-mode-checkout.fabriku.biz.id';
    $this->post($base.'/keranjang', ['product_id' => $product->id, 'quantity' => 2])->assertRedirect('/keranjang');
    $payload = [
        'idempotency_key' => (string) Str::uuid(),
        'name' => 'Pembeli', 'phone' => '081234567890', 'unit_price' => 1,
    ];
    $this->post($base.'/checkout', $payload)->assertRedirect('/keranjang')->assertSessionHasNoErrors();
    $this->post($base.'/checkout', $payload)->assertRedirect('/keranjang');
    $order = SalesOrder::withoutGlobalScopes()->sole();
    expect((float) $order->total_amount)->toBe(10000.0)
        ->and($order->status)->toBe('draft')
        ->and($order->items()->sole()->inventory_item_id)->toBe($item->id)
        ->and($item->refresh()->current_quantity)->toBe(10)
        ->and((float) $item->unit_cost)->toBe(1234.0);
})->with([false, true]);
