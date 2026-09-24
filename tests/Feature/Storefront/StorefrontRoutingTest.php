<?php

namespace Tests\Feature\Storefront;

use App\Models\BusinessSite;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontRoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_apex_domain_redirects_to_fabriku_id(): void
    {
        $response = $this->get('http://fabriku.biz.id/');
        $response->assertStatus(301);
        $response->assertRedirect('https://fabriku.id');

        $responseWww = $this->get('http://www.fabriku.biz.id/');
        $responseWww->assertStatus(301);
        $responseWww->assertRedirect('https://fabriku.id');
    }

    public function test_subdomain_resolves_storefront_with_cache_header(): void
    {
        $tenant = Tenant::factory()->create(['name' => 'Toko Sepatu Berkah']);
        $site = BusinessSite::factory()->create([
            'tenant_id' => $tenant->id,
            'slug' => 'sepatu-berkah',
            'status' => 'published',
            'profile' => [
                'name' => 'Toko Sepatu Berkah',
                'description' => 'Toko sepatu kulit lokal berkualitas',
            ],
        ]);

        $response = $this->get('http://sepatu-berkah.fabriku.biz.id/');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
        $response->assertHeader('Cache-Control', 'max-age=60, public');
        $response->assertSee('Toko Sepatu Berkah');
    }

    public function test_localhost_subdomain_resolves_storefront(): void
    {
        $tenant = Tenant::factory()->create(['name' => 'Bengkel Motor']);
        BusinessSite::factory()->create([
            'tenant_id' => $tenant->id,
            'slug' => 'bengkel-motor',
            'mode' => 'jasa',
            'status' => 'published',
            'profile' => [
                'name' => 'Bengkel Motor',
            ],
        ]);

        $response = $this->get('http://bengkel-motor.localhost/');
        $response->assertStatus(200);
        $response->assertSee('Bengkel Motor');
    }

    public function test_custom_domain_resolves_only_when_active(): void
    {
        $tenant = Tenant::factory()->create(['name' => 'Brand Keren']);
        BusinessSite::factory()->create([
            'tenant_id' => $tenant->id,
            'slug' => 'brand-keren',
            'custom_domain' => 'brandkeren.com',
            'domain_status' => 'active',
            'status' => 'published',
            'profile' => [
                'name' => 'Brand Keren Official',
            ],
        ]);

        $responseActive = $this->get('http://brandkeren.com/');
        $responseActive->assertStatus(200);
        $responseActive->assertSee('Brand Keren Official');

        // Another domain pending verification returns 404
        BusinessSite::factory()->create([
            'tenant_id' => Tenant::factory()->create()->id,
            'slug' => 'brand-pending',
            'custom_domain' => 'brandpending.com',
            'domain_status' => 'pending',
            'status' => 'published',
        ]);

        $responsePending = $this->get('http://brandpending.com/');
        $responsePending->assertStatus(404);
    }

    public function test_unknown_subdomain_returns_404(): void
    {
        $response = $this->get('http://toko-tidak-ada.fabriku.biz.id/');
        $response->assertStatus(404);
    }

    public function test_reserved_subdomain_returns_404(): void
    {
        $response = $this->get('http://api.fabriku.biz.id/');
        $response->assertStatus(404);
    }

    public function test_suspended_site_returns_403(): void
    {
        $tenant = Tenant::factory()->create();
        BusinessSite::factory()->create([
            'tenant_id' => $tenant->id,
            'slug' => 'toko-nakal',
            'status' => 'suspended',
        ]);

        $response = $this->get('http://toko-nakal.fabriku.biz.id/');
        $response->assertStatus(403);
    }

    public function test_robots_txt_and_sitemap(): void
    {
        $tenant = Tenant::factory()->create();
        $site = BusinessSite::factory()->create([
            'tenant_id' => $tenant->id,
            'slug' => 'toko-seo',
            'status' => 'published',
        ]);

        $robots = $this->get('http://toko-seo.fabriku.biz.id/robots.txt');
        $robots->assertStatus(200);
        $robots->assertSee('Allow: /');
        $robots->assertSee('Disallow: /keranjang');
        $robots->assertSee('Sitemap:');

        $sitemap = $this->get('http://toko-seo.fabriku.biz.id/sitemap.xml');
        $sitemap->assertStatus(200);
        $sitemap->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $sitemap->assertSee('<urlset', false);
    }
}
