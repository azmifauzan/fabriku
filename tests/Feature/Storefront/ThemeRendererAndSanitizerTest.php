<?php

namespace Tests\Feature\Storefront;

use App\Models\BusinessSite;
use App\Models\SiteContentPage;
use App\Models\SiteProduct;
use App\Models\SiteThemeVersion;
use App\Models\Tenant;
use App\Services\Storefront\HtmlSanitizerService;
use App\Services\Storefront\ThemeRenderer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class ThemeRendererAndSanitizerTest extends TestCase
{
    use RefreshDatabase;

    public function test_html_sanitizer_removes_scripts_iframes_forms_and_event_handlers(): void
    {
        $sanitizer = app(HtmlSanitizerService::class);

        $dirtyHtml = <<<'HTML'
<div class="hero-section" data-fb-section="hero">
    <script>alert("xss")</script>
    <iframe src="https://evil.com"></iframe>
    <form action="https://phishing.com/steal"><input type="text" name="pwd"></form>
    <a href="javascript:alert('pwned')" onmouseover="alert('hover')">Klik Disini</a>
    <img src="data:text/html;base64,PHNjcmlwdD4=" onerror="alert('img')">
    <h1 data-fb-text="title" class="text-2xl font-bold">Judul Bersih</h1>
    <svg class="w-6 h-6" viewBox="0 0 24 24"><path d="M0 0h24v24H0z" fill="none"/></svg>
</div>
HTML;

        $clean = $sanitizer->sanitize($dirtyHtml);

        $this->assertStringNotContainsString('<script', $clean);
        $this->assertStringNotContainsString('<iframe', $clean);
        $this->assertStringNotContainsString('<form', $clean);
        $this->assertStringNotContainsString('onmouseover', $clean);
        $this->assertStringNotContainsString('onerror', $clean);
        $this->assertStringNotContainsString('javascript:', $clean);

        // Safe elements preserved
        $this->assertStringContainsString('Judul Bersih', $clean);
        $this->assertStringContainsString('data-fb-text="title"', $clean);
        $this->assertStringContainsString('data-fb-section="hero"', $clean);
        $this->assertStringContainsString('<svg', $clean);
        $this->assertStringContainsString('<path', $clean);
    }

    public function test_imported_css_cannot_load_remote_assets_or_execute_legacy_syntax(): void
    {
        $sanitizer = app(HtmlSanitizerService::class);

        $this->assertSame('.hero{color:#123456}', $sanitizer->sanitizeCss('.hero{color:#123456}'));
        $this->assertSame('', $sanitizer->sanitizeCss('.hero{background:url(https://tracker.invalid/pixel)}'));
        $this->assertSame('', $sanitizer->sanitizeCss('.hero{width:expression(alert(1))}'));
    }

    public function test_imported_content_page_has_seo_footer_link_and_sanitized_html(): void
    {
        $tenant = Tenant::factory()->create(['name' => 'Kue Mama']);
        $site = BusinessSite::factory()->create([
            'tenant_id' => $tenant->id,
            'slug' => 'kue-mama',
            'status' => 'published',
            'profile' => ['name' => 'Kue Mama'],
        ]);
        $page = SiteContentPage::create([
            'tenant_id' => $tenant->id,
            'business_site_id' => $site->id,
            'slug' => 'kebijakan-pemesanan',
            'title' => 'Kebijakan Pemesanan',
            'seo_title' => 'Cara Memesan Kue',
            'seo_description' => 'Informasi pemesanan kue rumahan.',
            'html' => '<main><h1>Pesan di sini</h1><script>alert(1)</script><form><input></form></main>',
            'css' => '.imported{color:#123456}',
            'show_in_footer' => true,
            'is_published' => true,
        ]);

        $this->get('http://kue-mama.fabriku.biz.id/halaman/kebijakan-pemesanan')
            ->assertOk()
            ->assertSee('<title>Cara Memesan Kue</title>', false)
            ->assertSee('name="description" content="Informasi pemesanan kue rumahan."', false)
            ->assertSee('Pesan di sini')
            ->assertSee('/halaman/kebijakan-pemesanan')
            ->assertDontSee('<script', false)
            ->assertDontSee('<form', false)
            ->assertDontSee('alert(1)', false);

        $this->get('http://kue-mama.fabriku.biz.id/sitemap.xml')
            ->assertOk()
            ->assertSee('/halaman/kebijakan-pemesanan');

        $this->assertSame($site->id, $page->fresh()->business_site_id);
    }

    public function test_theme_renderer_injects_css_variables_and_renders_slots(): void
    {
        $tenant = Tenant::factory()->create(['name' => 'Kue Mama']);
        $site = BusinessSite::factory()->create([
            'tenant_id' => $tenant->id,
            'slug' => 'kue-mama',
            'mode' => 'produk',
            'profile' => [
                'name' => 'Kue Mama Homemade',
                'description' => 'Aneka kue basah dan kering tradisional',
                'whatsapp' => '081234567890',
            ],
        ]);

        $themeVersion = SiteThemeVersion::create([
            'tenant_id' => $tenant->id,
            'business_site_id' => $site->id,
            'source' => 'catalog',
            'version' => 1,
            'theme' => [
                'css_variables' => [
                    '--fb-primary' => '#b91c1c',
                    '--fb-accent' => '#f87171',
                    '--fb-bg' => '#fef2f2',
                ],
            ],
            'sections' => [
                [
                    'id' => 'sec-hero',
                    'type' => 'hero',
                    'sort' => 1,
                    'is_visible' => true,
                    'variables' => [
                        'hero-title' => 'Kue Tradisional Terbaik',
                    ],
                    'html' => '<section data-fb-section="hero"><h1 data-fb-text="hero-title">Default Title</h1><div data-fb-slot="whatsapp-button"></div><div data-fb-slot="lead-form"></div><form action="/phishing"><input name="password"></form></section>',
                ],
                [
                    'id' => 'sec-hidden',
                    'type' => 'about',
                    'sort' => 2,
                    'is_visible' => false,
                    'html' => '<section data-fb-section="about"><h2>Tentang Rahasia</h2></section>',
                ],
            ],
        ]);

        $renderer = app(ThemeRenderer::class);
        $html = $renderer->render($site, $themeVersion, [
            'page' => 'home',
            'errors' => new ViewErrorBag,
        ]);

        // Check CSS variables injection
        $this->assertStringContainsString('--fb-primary: #b91c1c;', $html);
        $this->assertStringContainsString('--fb-accent: #f87171;', $html);
        $this->assertStringContainsString('--fb-bg: #fef2f2;', $html);

        // Check variable replacement
        $this->assertStringContainsString('Kue Tradisional Terbaik', $html);
        $this->assertStringNotContainsString('Default Title', $html);

        // Check hidden section is not rendered
        $this->assertStringNotContainsString('Tentang Rahasia', $html);

        // Check slot replacement (whatsapp-button slot)
        $this->assertStringContainsString('wa.me/6281234567890', $html);
        $this->assertStringContainsString('Hubungi via WhatsApp', $html);
        $this->assertStringContainsString('action="/prospek"', $html);
        $this->assertStringNotContainsString('/phishing', $html);

        // Check footer attribution
        $this->assertStringContainsString('Dibuat dengan Fabriku', $html);
        $this->assertStringContainsString('Laporkan situs', $html);
        $this->assertStringContainsString('hidden md:flex items-center', $html);
        $this->assertStringContainsString('md:hidden overflow-x-auto', $html);
    }

    public function test_theme_renderer_deduplicates_unstyled_navigation_slots(): void
    {
        $tenant = Tenant::factory()->create(['name' => 'D Garment']);
        $site = BusinessSite::factory()->create([
            'tenant_id' => $tenant->id,
            'slug' => 'd-garment',
            'mode' => 'produk',
            'profile' => ['name' => 'D Garment'],
        ]);
        $themeVersion = SiteThemeVersion::create([
            'tenant_id' => $tenant->id,
            'business_site_id' => $site->id,
            'source' => 'catalog',
            'version' => 1,
            'theme' => [
                'shell_html' => '<header data-fb-shell="header"><div data-fb-slot="nav"></div><div data-fb-slot="nav"></div></header>',
            ],
            'sections' => [],
        ]);

        $html = app(ThemeRenderer::class)->render($site, $themeVersion, ['page' => 'home', 'errors' => new ViewErrorBag]);

        $this->assertSame(1, substr_count($html, 'aria-label="Navigasi toko"'));
    }

    private function featuredSlotHtml(): string
    {
        return '<section><figure data-fb-slot="featured-product"><p>Ilustrasi cadangan</p></figure></section>';
    }

    public function test_featured_product_slot_is_a_slideshow_of_distinct_photos_in_catalog_order(): void
    {
        $tenant = Tenant::factory()->create();
        $site = BusinessSite::factory()->create(['tenant_id' => $tenant->id]);
        SiteProduct::factory()->create(['tenant_id' => $tenant->id, 'title' => 'Tanpa Foto', 'slug' => 'tanpa-foto', 'sort' => 1]);
        $first = SiteProduct::factory()->create(['tenant_id' => $tenant->id, 'title' => 'Takoyaki Isi 4', 'slug' => 'takoyaki-isi-4', 'image_path' => 'https://cdn.example.test/takoyaki.jpg', 'sort' => 2]);
        SiteProduct::factory()->create(['tenant_id' => $tenant->id, 'title' => 'Takoyaki Isi 8', 'slug' => 'takoyaki-isi-8', 'image_path' => 'https://cdn.example.test/takoyaki.jpg', 'sort' => 3]);
        SiteProduct::factory()->create(['tenant_id' => $tenant->id, 'title' => 'Puding Buah', 'slug' => 'puding-buah', 'image_path' => 'https://cdn.example.test/puding.jpg', 'sort' => 4]);

        $html = app(ThemeRenderer::class)->replaceSlots($this->featuredSlotHtml(), ['site' => $site, 'products' => $site->tenant->siteProducts()->orderBy('sort')->get()]);

        $this->assertSame(2, substr_count($html, 'class="fb-slide"'));
        $this->assertLessThan(strpos($html, 'puding.jpg'), strpos($html, 'takoyaki.jpg'));
        $this->assertStringContainsString('href="/produk/'.$first->slug.'"', $html);
        $this->assertStringNotContainsString('takoyaki-isi-8', $html);
        $this->assertStringNotContainsString('tanpa-foto', $html);
        $this->assertStringContainsString('@keyframes fb-hero-2', $html);
        $this->assertStringContainsString('prefers-reduced-motion', $html);
        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('Ilustrasi cadangan', $html);
    }

    public function test_featured_product_slot_shows_at_most_five_slides_and_is_static_for_one(): void
    {
        $tenant = Tenant::factory()->create();
        $site = BusinessSite::factory()->create(['tenant_id' => $tenant->id]);
        foreach (range(1, 7) as $n) {
            SiteProduct::factory()->create(['tenant_id' => $tenant->id, 'slug' => "produk-{$n}", 'image_path' => "https://cdn.example.test/p{$n}.jpg", 'sort' => $n]);
        }
        $renderer = app(ThemeRenderer::class);
        $products = SiteProduct::withoutGlobalScopes()->where('tenant_id', $tenant->id)->orderBy('sort')->get();

        $many = $renderer->replaceSlots($this->featuredSlotHtml(), ['site' => $site, 'products' => $products]);
        $this->assertSame(5, substr_count($many, 'class="fb-slide"'));
        $this->assertSame(5, substr_count($many, '<i style='));
        $this->assertStringNotContainsString('p6.jpg', $many);

        $one = $renderer->replaceSlots($this->featuredSlotHtml(), ['site' => $site, 'products' => $products->take(1)]);
        $this->assertSame(1, substr_count($one, 'class="fb-slide"'));
        $this->assertStringNotContainsString('@keyframes', $one);
        $this->assertStringNotContainsString('fb-dots"', $one);
    }

    public function test_featured_product_slot_keeps_the_designer_fallback_when_no_product_has_an_image(): void
    {
        $tenant = Tenant::factory()->create();
        $site = BusinessSite::factory()->create(['tenant_id' => $tenant->id]);
        SiteProduct::factory()->create(['tenant_id' => $tenant->id, 'image_path' => null]);

        $html = app(ThemeRenderer::class)->replaceSlots($this->featuredSlotHtml(), ['site' => $site, 'products' => SiteProduct::withoutGlobalScopes()->where('tenant_id', $tenant->id)->get()]);

        $this->assertSame('<section><p>Ilustrasi cadangan</p></section>', $html);
        $this->assertSame('<section></section>', app(ThemeRenderer::class)->replaceSlots('<section><figure data-fb-slot="featured-product"></figure></section>', ['site' => $site, 'products' => collect()]));
    }

    public function test_home_featured_slot_ignores_hidden_and_other_tenant_products(): void
    {
        $tenant = Tenant::factory()->create();
        $site = BusinessSite::factory()->create(['tenant_id' => $tenant->id, 'slug' => 'toko-hero', 'status' => 'published', 'mode' => 'produk']);
        $version = SiteThemeVersion::create([
            'tenant_id' => $tenant->id, 'business_site_id' => $site->id, 'source' => 'manual', 'version' => 1,
            'theme' => ['preset' => 'ruang'],
            'sections' => [['type' => 'hero', 'sort' => 0, 'is_visible' => true, 'variables' => [], 'html' => $this->featuredSlotHtml()]],
        ]);
        $site->update(['active_theme_version_id' => $version->id]);
        SiteProduct::factory()->create(['tenant_id' => $tenant->id, 'is_visible' => false, 'sort' => 0, 'image_path' => 'https://cdn.example.test/hidden.jpg']);
        SiteProduct::factory()->create(['tenant_id' => Tenant::factory()->create()->id, 'sort' => 0, 'image_path' => 'https://cdn.example.test/other-tenant.jpg']);

        $this->get('http://toko-hero.fabriku.biz.id/')->assertOk()
            ->assertSee('Ilustrasi cadangan')
            ->assertDontSee('hidden.jpg')->assertDontSee('other-tenant.jpg');

        SiteProduct::factory()->create(['tenant_id' => $tenant->id, 'sort' => 5, 'image_path' => 'https://cdn.example.test/visible.jpg']);

        $this->get('http://toko-hero.fabriku.biz.id/')->assertOk()
            ->assertSee('visible.jpg')->assertDontSee('Ilustrasi cadangan')
            ->assertDontSee('hidden.jpg')->assertDontSee('other-tenant.jpg');
    }

    public function test_storefront_favicon_uses_each_sites_logo_and_keeps_platform_fallback(): void
    {
        foreach ([
            ['dsfa-icons', 'https://fabriku.id/storage/logos/dsfa-homemade.png'],
            ['other-icons', 'https://cdn.example.test/other-logo.png?size=64&v=2'],
            ['no-logo-icons', null],
        ] as [$slug, $logo]) {
            $tenant = Tenant::factory()->create();
            BusinessSite::factory()->create(['tenant_id' => $tenant->id, 'slug' => $slug, 'profile' => ['name' => $slug, 'logo_url' => $logo]]);
            foreach (['/', '/produk'] as $path) {
                $response = $this->get('https://'.$slug.'.fabriku.biz.id'.$path)->assertOk();
                $dom = new \DOMDocument;
                @$dom->loadHTML($response->getContent());
                $xpath = new \DOMXPath($dom);
                $this->assertSame($logo ?? '/favicon.ico', $xpath->evaluate('string(//link[@rel="icon"]/@href)'));
                $this->assertSame($logo ?? '/apple-touch-icon.png', $xpath->evaluate('string(//link[@rel="apple-touch-icon"]/@href)'));
            }
        }
        $this->get('/login')->assertOk()->assertSee('href="/favicon.svg"', false);
    }

    public function test_cart_badge_is_server_rendered_and_scoped_to_the_current_store(): void
    {
        $one = BusinessSite::factory()->create(['slug' => 'cart-store-one']);
        $two = BusinessSite::factory()->create(['slug' => 'cart-store-two']);
        $this->withSession([
            'storefront_cart.'.$one->tenant_id => [11 => 2, 12 => 3],
            'storefront_cart.'.$two->tenant_id => [99 => 1],
        ])->get('https://cart-store-one.fabriku.biz.id/')->assertOk()
            ->assertSee('aria-label="Keranjang belanja, 5 barang"', false);
        $this->get('https://cart-store-two.fabriku.biz.id/produk')->assertOk()
            ->assertSee('aria-label="Keranjang belanja, 1 barang"', false);
    }
}
