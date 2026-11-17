<?php

namespace Tests\Feature\Storefront;

use App\Models\BusinessSite;
use App\Models\InventoryItem;
use App\Models\SiteProduct;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class GoogleAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_the_configured_store_receives_analytics_with_a_fresh_csp_nonce(): void
    {
        $site = BusinessSite::factory()->create([
            'tenant_id' => Tenant::factory()->create()->id,
            'slug' => 'tracked-shop',
            'custom_domain' => 'tracked.example',
            'domain_status' => 'active',
            'status' => 'published',
            'ga_measurement_id' => 'G-TEST123',
        ]);

        $firstNonce = null;
        foreach (['/', '/produk', '/keranjang', '/privasi'] as $path) {
            $response = $this->get('https://tracked.example'.$path)->assertOk();
            $response->assertSee('https://www.googletagmanager.com/gtag/js?id=G-TEST123', false)
                ->assertSee('allow_google_signals: false', false)
                ->assertSee('allow_ad_personalization_signals: false', false);
            $csp = $response->headers->get('Content-Security-Policy');
            $this->assertSame(1, preg_match("/script-src 'nonce-([^']+)'/", $csp, $matches));
            $this->assertStringContainsString('nonce="'.$matches[1].'"', $response->getContent());
            $this->assertStringNotContainsString("script-src 'unsafe-inline'", $csp);
            $this->assertStringContainsString('connect-src https://*.google-analytics.com', $csp);
            $this->assertNotSame($firstNonce, $matches[1]);
            $firstNonce = $matches[1];
        }

        $this->get('https://tracked.example/privasi')->assertSee('Statistik kunjungan');

        $site->update(['custom_domain' => 'other.example', 'ga_measurement_id' => null]);
        $response = $this->get('https://other.example/')->assertOk()->assertDontSee('googletagmanager.com');
        $this->assertStringContainsString("script-src 'none'; connect-src 'none'", $response->headers->get('Content-Security-Policy'));
        $this->get('https://other.example/privasi')->assertDontSee('Statistik kunjungan');
    }

    public function test_invalid_measurement_ids_cannot_enable_scripts(): void
    {
        $site = new BusinessSite(['ga_measurement_id' => "G-TEST');alert(1)//"]);
        $this->assertNull($site->googleAnalyticsMeasurementId());
        $site->ga_measurement_id = null;
        $this->assertNull($site->googleAnalyticsMeasurementId());
        $site->ga_measurement_id = 'G-TEST123';
        $this->assertSame('G-TEST123', $site->googleAnalyticsMeasurementId());
    }

    public function test_manager_can_set_and_clear_only_their_own_site_id_without_changing_other_data(): void
    {
        $manager = User::factory()->manager()->create();
        $site = BusinessSite::factory()->create(['tenant_id' => $manager->tenant_id]);
        $other = BusinessSite::factory()->create();
        $before = $site->fresh()->getAttributes();
        $this->actingAs($manager)->post('/website/analytics', [
            'ga_measurement_id' => 'G-TEST123',
            'tenant_id' => $other->tenant_id,
            'id' => $other->id,
            'profile' => ['name' => 'Changed'],
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('G-TEST123', $site->fresh()->ga_measurement_id);
        $this->assertNull($other->fresh()->ga_measurement_id);
        $this->assertSame(
            array_diff_key($before, array_flip(['updated_at', 'ga_measurement_id'])),
            array_diff_key($site->fresh()->getAttributes(), array_flip(['updated_at', 'ga_measurement_id'])),
        );
        foreach (['<script>alert(1)</script>', 'G-ABC\n', 'GTM-ABC123', 'UA-12345', str_repeat('G', 100)] as $invalid) {
            $this->post('/website/analytics', ['ga_measurement_id' => $invalid])->assertSessionHasErrors('ga_measurement_id');
            $this->assertSame('G-TEST123', $site->fresh()->ga_measurement_id);
        }
        $this->post('/website/analytics', ['ga_measurement_id' => ''])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertNull($site->fresh()->ga_measurement_id);
        $this->get('https://'.$site->slug.'.fabriku.biz.id/')->assertOk()->assertDontSee('googletagmanager.com');
    }

    public function test_guests_and_staff_cannot_change_analytics(): void
    {
        $site = BusinessSite::factory()->create();
        $this->post('/website/analytics', ['ga_measurement_id' => 'G-TEST123'])->assertRedirect(route('login'));
        $staff = User::factory()->forTenant($site->tenant_id)->create(['role' => 'staff']);
        $this->actingAs($staff)->post('/website/analytics', ['ga_measurement_id' => 'G-TEST123'])->assertForbidden();
        $this->assertNull($site->fresh()->ga_measurement_id);
    }

    public function test_cart_events_are_tenant_scoped_one_time_and_only_emitted_after_success(): void
    {
        $site = BusinessSite::factory()->create(['slug' => 'events-shop', 'ga_measurement_id' => 'G-TEST123']);
        $other = BusinessSite::factory()->create(['slug' => 'other-events-shop', 'ga_measurement_id' => 'G-OTHER123']);
        $item = InventoryItem::factory()->create([
            'tenant_id' => $site->tenant_id, 'selling_price' => 7000, 'current_quantity' => 100,
            'reserved_quantity' => 0, 'status' => 'available',
        ]);
        $product = SiteProduct::factory()->create(['tenant_id' => $site->tenant_id, 'inventory_item_id' => $item->id, 'title' => 'Oreo cup']);
        $base = 'https://events-shop.fabriku.biz.id';
        $eventKey = 'storefront_analytics.'.$site->id;
        $this->post($base.'/keranjang', ['product_id' => $product->id, 'quantity' => 0])->assertSessionHasErrors();
        $this->assertNull(session($eventKey));
        $this->post($base.'/keranjang', ['product_id' => $product->id, 'quantity' => 2])->assertRedirect('/keranjang');
        $this->assertSame(['name' => 'add_to_cart', 'params' => [
            'currency' => 'IDR', 'value' => 14000.0,
            'items' => [['item_id' => (string) $product->id, 'item_name' => 'Oreo cup', 'price' => 7000.0, 'quantity' => 2]],
        ]], session($eventKey));
        $this->get('https://other-events-shop.fabriku.biz.id/keranjang')->assertOk()->assertDontSee('add_to_cart');
        $this->post($base.'/keranjang', ['product_id' => $product->id, 'quantity' => 1])->assertRedirect('/keranjang');
        $this->get($base.'/keranjang')->assertOk()->assertSee('add_to_cart')->assertDontSee('order_request');
        $this->get($base.'/keranjang')->assertDontSee('add_to_cart');

        $payload = ['idempotency_key' => (string) Str::uuid(), 'name' => 'Private Buyer', 'phone' => '081234567890', 'address' => 'Private Street'];
        $this->post($base.'/checkout', $payload)->assertSessionHasNoErrors();
        $this->assertSame(['name' => 'order_request', 'params' => ['currency' => 'IDR', 'value' => 21000.0]], session($eventKey));
        $response = $this->get($base.'/keranjang')->assertOk()->assertSee('order_request')->assertDontSee('Private Buyer')->assertDontSee('Private Street');
        $this->assertNull(session($eventKey));
        $this->post($base.'/checkout', $payload)->assertSessionHasNoErrors();
        $this->get($base.'/keranjang')->assertDontSee('order_request');

        if ($output = getenv('STOREFRONT_ANALYTICS_HTML')) {
            file_put_contents($output, $response->getContent());
        }
        $site->update(['ga_measurement_id' => null]);
        $this->post($base.'/keranjang', ['product_id' => $product->id, 'quantity' => 1])->assertRedirect();
        $this->assertNull(session($eventKey));
    }
}
