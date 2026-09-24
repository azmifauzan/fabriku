<?php

namespace Tests\Feature\Storefront;

use App\Models\BusinessSite;
use App\Models\InventoryItem;
use App\Models\Service;
use App\Models\SiteProduct;
use App\Models\Tenant;
use App\Services\Storefront\Storefront;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_site_product_starting_price_and_availability_labels(): void
    {
        $tenant = Tenant::factory()->create();

        $item1 = InventoryItem::factory()->create([
            'tenant_id' => $tenant->id,
            'product_code' => 'PRD-001',
            'selling_price' => 75000,
            'current_quantity' => 10,
            'reserved_quantity' => 2,
            'minimum_stock' => 5,
        ]);

        $item2 = InventoryItem::factory()->create([
            'tenant_id' => $tenant->id,
            'product_code' => 'PRD-001',
            'selling_price' => 85000,
            'current_quantity' => 5,
            'reserved_quantity' => 0,
            'minimum_stock' => 5,
        ]);

        $siteProduct = SiteProduct::create([
            'tenant_id' => $tenant->id,
            'product_code' => 'PRD-001',
            'slug' => 'baju-batik-modern',
            'title' => 'Baju Batik Modern',
            'description' => 'Batik katun halus premium.',
        ]);

        $this->assertEquals(75000, $siteProduct->getStartingPrice());
        $this->assertTrue($siteProduct->hasPriceVariation());
        $this->assertEquals(13, $siteProduct->getTotalAvailableStock()); // (10-2) + (5-0) = 13
        $this->assertEquals('Tersedia', $siteProduct->getAvailabilityStatus());

        // Update to limited stock (total stock 3 <= minimum_stock 5)
        $item1->update([
            'current_quantity' => 3,
            'reserved_quantity' => 0,
        ]);
        $item2->update([
            'current_quantity' => 0,
            'reserved_quantity' => 0,
        ]);
        $this->assertEquals(3, $siteProduct->getTotalAvailableStock());
        $this->assertEquals('Stok terbatas', $siteProduct->getAvailabilityStatus());

        // Out of stock
        $item1->update(['current_quantity' => 0, 'reserved_quantity' => 0]);
        $item2->update(['current_quantity' => 0, 'reserved_quantity' => 0]);
        $this->assertEquals('Habis', $siteProduct->getAvailabilityStatus());
    }

    public function test_two_tenants_catalog_isolation(): void
    {
        $tenant1 = Tenant::factory()->create(['name' => 'Toko Satu']);
        $tenant2 = Tenant::factory()->create(['name' => 'Toko Dua']);

        $site1 = BusinessSite::factory()->create([
            'tenant_id' => $tenant1->id,
            'slug' => 'toko-satu',
            'status' => 'published',
        ]);
        $site2 = BusinessSite::factory()->create([
            'tenant_id' => $tenant2->id,
            'slug' => 'toko-dua',
            'status' => 'published',
        ]);

        $p1 = SiteProduct::create([
            'tenant_id' => $tenant1->id,
            'product_code' => 'P1',
            'slug' => 'produk-satu',
            'title' => 'Produk Khusus Toko Satu',
        ]);

        $p2 = SiteProduct::create([
            'tenant_id' => $tenant2->id,
            'product_code' => 'P2',
            'slug' => 'produk-dua',
            'title' => 'Produk Khusus Toko Dua',
        ]);

        // Scoped helper verification
        $t1Products = Storefront::for($tenant1)->products()->pluck('title');
        $this->assertTrue($t1Products->contains('Produk Khusus Toko Satu'));
        $this->assertFalse($t1Products->contains('Produk Khusus Toko Dua'));

        // HTTP Storefront 1
        $res1 = $this->get('http://toko-satu.fabriku.biz.id/produk');
        $res1->assertStatus(200);
        $res1->assertSee('Produk Khusus Toko Satu');
        $res1->assertDontSee('Produk Khusus Toko Dua');

        // HTTP Storefront 2
        $res2 = $this->get('http://toko-dua.fabriku.biz.id/produk');
        $res2->assertStatus(200);
        $res2->assertSee('Produk Khusus Toko Dua');
        $res2->assertDontSee('Produk Khusus Toko Satu');
    }

    public function test_service_enhancements_and_public_scope(): void
    {
        $tenant = Tenant::factory()->create();

        $publicService = Service::create([
            'tenant_id' => $tenant->id,
            'code' => 'SVC-GAUN',
            'name' => 'Jasa Jahit Gaun',
            'price' => 150000,
            'is_active' => true,
            'is_public' => true,
            'price_label' => 'mulai_dari',
            'slug' => 'jasa-jahit-gaun',
            'public_description' => 'Menerima jahitan gaun pesta custom.',
        ]);

        $privateService = Service::create([
            'tenant_id' => $tenant->id,
            'code' => 'SVC-SECRET',
            'name' => 'Jasa Internal Rahasia',
            'price' => 50000,
            'is_active' => true,
            'is_public' => false,
            'slug' => 'internal-service',
        ]);

        $this->assertEquals('Mulai dari Rp 150.000', $publicService->getPriceDisplay());

        $contactService = Service::create([
            'tenant_id' => $tenant->id,
            'code' => 'SVC-DESAIN',
            'name' => 'Konsultasi Desain',
            'price' => 0,
            'is_active' => true,
            'is_public' => true,
            'price_label' => 'hubungi_kami',
            'slug' => 'konsultasi-desain',
        ]);
        $this->assertEquals('Hubungi Kami', $contactService->getPriceDisplay());

        // Scope test
        $publicServices = Storefront::for($tenant)->services()->pluck('name');
        $this->assertTrue($publicServices->contains('Jasa Jahit Gaun'));
        $this->assertTrue($publicServices->contains('Konsultasi Desain'));
        $this->assertFalse($publicServices->contains('Jasa Internal Rahasia'));
    }

    public function test_storefront_product_and_service_detail_routes(): void
    {
        $tenant = Tenant::factory()->create();
        $site = BusinessSite::factory()->create([
            'tenant_id' => $tenant->id,
            'slug' => 'toko-lengkap',
            'mode' => 'gabungan',
            'status' => 'published',
            'profile' => [
                'name' => 'Toko Lengkap',
                'whatsapp' => '081234567890',
            ],
        ]);

        $product = SiteProduct::create([
            'tenant_id' => $tenant->id,
            'product_code' => 'KEMEJA-01',
            'slug' => 'kemeja-pria-premium',
            'title' => 'Kemeja Pria Premium',
            'description' => 'Kemeja katun premium lengan panjang.',
        ]);

        InventoryItem::factory()->create([
            'tenant_id' => $tenant->id,
            'product_code' => 'KEMEJA-01',
            'selling_price' => 125000,
            'current_quantity' => 20,
            'reserved_quantity' => 0,
        ]);

        $service = Service::create([
            'tenant_id' => $tenant->id,
            'code' => 'SVC-KILAT',
            'name' => 'Jahit Kilat 24 Jam',
            'price' => 200000,
            'is_active' => true,
            'is_public' => true,
            'price_label' => 'tetap',
            'slug' => 'jahit-kilat-24-jam',
            'public_description' => 'Layanan jahit cepat selesai dalam 24 jam.',
        ]);

        // Product detail page
        $resProduct = $this->get('http://toko-lengkap.fabriku.biz.id/produk/kemeja-pria-premium');
        $resProduct->assertStatus(200);
        $resProduct->assertSee('Kemeja Pria Premium');
        $resProduct->assertSee('Rp 125.000');
        $resProduct->assertSee('Tersedia');

        // Service detail page
        $resService = $this->get('http://toko-lengkap.fabriku.biz.id/layanan/jahit-kilat-24-jam');
        $resService->assertStatus(200);
        $resService->assertSee('Jahit Kilat 24 Jam');
        $resService->assertSee('Rp 200.000');
        $resService->assertSee('Layanan jahit cepat selesai dalam 24 jam.');
    }
}
