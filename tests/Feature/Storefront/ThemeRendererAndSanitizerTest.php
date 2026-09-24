<?php

namespace Tests\Feature\Storefront;

use App\Models\BusinessSite;
use App\Models\SiteThemeVersion;
use App\Models\Tenant;
use App\Services\Storefront\HtmlSanitizerService;
use App\Services\Storefront\ThemeRenderer;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
                    'html' => '<section data-fb-section="hero"><h1 data-fb-text="hero-title">Default Title</h1><div data-fb-slot="whatsapp-button"></div></section>',
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
        $html = $renderer->render($site, $themeVersion, ['page' => 'home']);

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

        // Check footer attribution
        $this->assertStringContainsString('Dibuat dengan Fabriku', $html);
        $this->assertStringContainsString('Laporkan situs', $html);
    }
}
