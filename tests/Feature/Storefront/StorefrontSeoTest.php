<?php

namespace Tests\Feature\Storefront;

use App\Models\BusinessSite;
use App\Models\InventoryItem;
use App\Models\SiteContentPage;
use App\Models\SiteProduct;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontSeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_metadata_and_schema_use_public_product_data_and_escape_script_content(): void
    {
        $site = BusinessSite::factory()->create([
            'slug' => 'seo-shop', 'status' => 'published',
            'profile' => ['name' => 'Toko SEO', 'address' => 'Alamat privat'],
            'seo' => ['description' => 'Deskripsi beranda'],
        ]);
        $item = InventoryItem::factory()->create([
            'tenant_id' => $site->tenant_id, 'selling_price' => 7000, 'current_quantity' => 10,
            'reserved_quantity' => 0, 'status' => 'available',
        ]);
        $product = SiteProduct::factory()->create([
            'tenant_id' => $site->tenant_id, 'inventory_item_id' => $item->id, 'slug' => 'oreo',
            'title' => 'Oreo </script><script>alert(1)</script>', 'description' => "Dessert Oreo\n cup kecil.",
            'image_path' => 'https://cdn.example.test/oreo.jpg',
        ]);
        foreach ([['menu-oreo', true, 'oreo'], ['other-menu', true, 'other-oreo'], ['private-menu', false, 'oreo']] as [$slug, $published, $selected]) {
            SiteContentPage::create([
                'tenant_id' => $site->tenant_id, 'business_site_id' => $site->id,
                'slug' => $slug, 'title' => $slug, 'is_published' => $published,
                'html' => '<div data-fb-slot="products" data-fb-products="'.$selected.'"></div>',
            ]);
        }
        $response = $this->get('https://seo-shop.fabriku.biz.id/produk/oreo')->assertOk();
        $dom = new DOMDocument;
        @$dom->loadHTML($response->getContent());
        $xpath = new DOMXPath($dom);
        $this->assertSame(1, $xpath->query('//nav[@aria-label="Menu terkait"]//a[@href="/halaman/menu-oreo"]')->length);
        $this->assertSame(0, $xpath->query('//nav[@aria-label="Menu terkait"]//a[@href="/halaman/other-menu"]')->length);
        $this->assertSame(0, $xpath->query('//a[@href="/halaman/private-menu"]')->length);
        $this->assertSame('Dessert Oreo cup kecil.', $xpath->evaluate('string(//meta[@name="description"]/@content)'));
        $this->assertSame('Dessert Oreo cup kecil.', $xpath->evaluate('string(//meta[@property="og:description"]/@content)'));
        $this->assertSame(0, $xpath->query('//script[not(@type="application/ld+json")]')->length);
        $schema = json_decode($xpath->evaluate('string(//script[@type="application/ld+json"])'), true, flags: JSON_THROW_ON_ERROR);
        [$organization, $schemaProduct, $breadcrumb] = $schema['@graph'];
        $this->assertSame('Toko SEO', $organization['name']);
        $this->assertArrayNotHasKey('address', $organization);
        $this->assertSame($product->title, $schemaProduct['name']);
        $this->assertSame('7000.00', $schemaProduct['offers']['price']);
        $this->assertSame('IDR', $schemaProduct['offers']['priceCurrency']);
        $this->assertSame('https://schema.org/InStock', $schemaProduct['offers']['availability']);
        $this->assertSame('https://seo-shop.fabriku.biz.id/produk/oreo', $schemaProduct['url']);
        $this->assertSame('BreadcrumbList', $breadcrumb['@type']);
        $this->assertSame([1, 2, 3], array_column($breadcrumb['itemListElement'], 'position'));

        $item->update(['is_open_order' => true, 'current_quantity' => 0, 'lead_time_days' => 3]);
        $this->get('https://seo-shop.fabriku.biz.id/produk/oreo')->assertSee('https:\/\/schema.org\/PreOrder', false);
        $item->update(['is_open_order' => false]);
        $unavailable = $this->get('https://seo-shop.fabriku.biz.id/produk/oreo')->assertOk();
        $this->assertStringNotContainsString('"price":"0.00"', $unavailable->getContent());
        $this->assertStringNotContainsString('schema.org\\/InStock', $unavailable->getContent());

        foreach (['/keranjang', '/lapor'] as $path) {
            $this->get('https://seo-shop.fabriku.biz.id'.$path)->assertOk()
                ->assertHeader('X-Robots-Tag', 'noindex, nofollow')->assertDontSee('application/ld+json', false);
        }
        $product->update(['is_visible' => false]);
        $this->get('https://seo-shop.fabriku.biz.id/produk/oreo')->assertNotFound();
        $other = SiteProduct::factory()->create();
        $this->get('https://seo-shop.fabriku.biz.id/produk/'.$other->slug)->assertNotFound();
    }
}
