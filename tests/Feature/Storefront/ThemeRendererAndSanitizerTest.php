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

    public function test_featured_product_slot_shows_first_catalog_product_that_has_an_image(): void
    {
        $tenant = Tenant::factory()->create();
        $site = BusinessSite::factory()->create(['tenant_id' => $tenant->id]);
        SiteProduct::factory()->create(['tenant_id' => $tenant->id, 'title' => 'Tanpa Foto', 'slug' => 'tanpa-foto', 'sort' => 1]);
        $second = SiteProduct::factory()->create(['tenant_id' => $tenant->id, 'title' => 'Takoyaki Isi', 'slug' => 'takoyaki-isi', 'image_path' => 'https://cdn.example.test/takoyaki.jpg', 'sort' => 2]);
        SiteProduct::factory()->create(['tenant_id' => $tenant->id, 'title' => 'Puding Buah', 'slug' => 'puding-buah', 'image_path' => 'https://cdn.example.test/puding.jpg', 'sort' => 3]);

        $html = app(ThemeRenderer::class)->replaceSlots($this->featuredSlotHtml(), ['site' => $site, 'products' => $site->tenant->siteProducts()->orderBy('sort')->get()]);

        $this->assertStringContainsString('https://cdn.example.test/takoyaki.jpg', $html);
        $this->assertStringContainsString('href="/produk/'.$second->slug.'"', $html);
        $this->assertStringNotContainsString('puding.jpg', $html);
        $this->assertStringNotContainsString('Ilustrasi cadangan', $html);
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
}
