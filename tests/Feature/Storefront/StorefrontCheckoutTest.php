<?php

namespace Tests\Feature\Storefront;

use App\Models\BusinessSite;
use App\Models\InventoryItem;
use App\Models\SalesOrder;
use App\Models\SiteProduct;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class StorefrontCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_uses_tenant_price_creates_draft_and_is_idempotent(): void
    {
        $tenant = Tenant::factory()->create();
        BusinessSite::factory()->create(['tenant_id' => $tenant->id, 'slug' => 'checkout-shop', 'mode' => 'produk', 'status' => 'published']);
        $product = SiteProduct::factory()->create(['tenant_id' => $tenant->id, 'product_code' => 'P-1', 'slug' => 'produk-1']);
        $item = InventoryItem::factory()->create(['tenant_id' => $tenant->id, 'product_code' => 'P-1', 'selling_price' => 25000, 'current_quantity' => 10, 'reserved_quantity' => 0, 'status' => 'available']);

        $this->post('http://checkout-shop.fabriku.biz.id/keranjang', ['product_id' => $product->id, 'quantity' => 2])->assertRedirect('/keranjang');
        $this->get('http://checkout-shop.fabriku.biz.id/keranjang')->assertOk()->assertSee('Kirim pesanan');
        $payload = ['idempotency_key' => (string) Str::uuid(), 'name' => 'Pembeli', 'phone' => '081234567890', 'email' => 'buyer@example.test'];
        $this->post('http://checkout-shop.fabriku.biz.id/checkout', $payload)->assertRedirect('/keranjang');
        $this->post('http://checkout-shop.fabriku.biz.id/checkout', $payload)->assertRedirect('/keranjang');

        $this->assertDatabaseCount('sales_orders', 1);
        $order = SalesOrder::withoutGlobalScopes()->firstOrFail();
        $this->assertSame($tenant->id, $order->tenant_id);
        $this->assertSame('draft', $order->status);
        $this->assertSame('unpaid', $order->payment_status);
        $this->assertSame('50000.00', $order->total_amount);
        $this->assertSame($item->id, $order->items()->first()->inventory_item_id);
        $this->assertSame('6281234567890', $order->customer->phone);
        $this->assertSame(0, (int) $item->fresh()->reserved_quantity);
    }

    public function test_checkout_rejects_another_tenants_product_and_insufficient_stock(): void
    {
        $tenant = Tenant::factory()->create();
        BusinessSite::factory()->create(['tenant_id' => $tenant->id, 'slug' => 'limited-shop', 'mode' => 'produk', 'status' => 'published']);
        $other = SiteProduct::factory()->create(['tenant_id' => Tenant::factory()->create()->id]);
        $this->post('http://limited-shop.fabriku.biz.id/keranjang', ['product_id' => $other->id, 'quantity' => 1])->assertNotFound();

        $product = SiteProduct::factory()->create(['tenant_id' => $tenant->id, 'product_code' => 'P-2', 'slug' => 'produk-2']);
        InventoryItem::factory()->create(['tenant_id' => $tenant->id, 'product_code' => 'P-2', 'selling_price' => 10000, 'current_quantity' => 1, 'reserved_quantity' => 0, 'status' => 'available']);
        $this->post('http://limited-shop.fabriku.biz.id/keranjang', ['product_id' => $product->id, 'quantity' => 2])->assertRedirect('/keranjang');
        $this->post('http://limited-shop.fabriku.biz.id/checkout', ['idempotency_key' => (string) Str::uuid(), 'name' => 'Pembeli', 'phone' => '081234567890'])
            ->assertSessionHasErrors('cart');
        $this->assertDatabaseCount('sales_orders', 0);
    }
}
