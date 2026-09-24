<?php

namespace App\Services\Storefront;

use App\Models\BusinessSite;
use App\Models\SiteThemeVersion;
use Illuminate\Support\Facades\View;

class ThemeRenderer
{
    public function __construct(
        protected HtmlSanitizerService $sanitizer
    ) {}

    /**
     * Render a complete storefront page.
     *
     * @param  array  $context  [
     *                          'page' => 'home'|'products'|'product_detail'|'services'|'service_detail'|'cart',
     *                          'page_content' => string|null,
     *                          'products' => Collection,
     *                          'services' => Collection,
     *                          'product' => SiteProduct|null,
     *                          'service' => Service|null,
     *                          'title' => string|null,
     *                          ]
     */
    public function render(BusinessSite $site, ?SiteThemeVersion $themeVersion, array $context = []): string
    {
        $context['site'] = $site;
        $page = $context['page'] ?? 'home';

        // 1. Resolve CSS variables
        $cssVars = $this->resolveCssVariables($site, $themeVersion);
        $cssBlock = $this->generateCssBlock($cssVars);

        // 2. Resolve Shell (Header & Footer) and sanitize
        $shell = $this->resolveShell($site, $themeVersion, $context);
        $headerHtml = $this->sanitizer->sanitize($shell['header']);
        $footerHtml = $this->sanitizer->sanitize($shell['footer']);

        // 3. Resolve Main Body Content and sanitize
        if ($page === 'home') {
            $rawContent = $this->renderHomeSections($site, $themeVersion, $context);
        } else {
            $rawContent = $context['page_content'] ?? '';
        }
        $mainContent = $this->sanitizer->sanitize($rawContent);

        // 4. Assemble HTML document
        $title = e($context['title'] ?? ($site->profile['name'] ?? $site->tenant->name ?? 'Toko Online'));
        $description = e($site->profile['description'] ?? 'Selamat datang di toko resmi kami.');
        $cssPath = $themeVersion?->css_path;

        return <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
    <meta name="description" content="{$description}">
    <script src="https://cdn.tailwindcss.com"></script>
    {$cssBlock}
    {$this->renderOptionalCssLink($cssPath)}
</head>
<body class="bg-[var(--fb-bg)] text-[var(--fb-text)] font-sans antialiased min-h-screen flex flex-col">
    {$headerHtml}
    <main class="flex-1">
        {$mainContent}
    </main>
    {$footerHtml}
</body>
</html>
HTML;
    }

    /**
     * Render Home sections from active theme version or fallback.
     */
    public function renderHomeSections(BusinessSite $site, ?SiteThemeVersion $themeVersion, array $context): string
    {
        $sections = $themeVersion?->sections ?? $this->getDefaultSections($site);
        usort($sections, fn ($a, $b) => ($a['sort'] ?? 0) <=> ($b['sort'] ?? 0));

        $html = '';
        foreach ($sections as $section) {
            if (isset($section['is_visible']) && ! $section['is_visible']) {
                continue;
            }

            $rawHtml = $section['html'] ?? '';
            $sectionType = $section['type'] ?? 'hero';

            // Replace text, image, link variables
            $replaced = $this->replaceVariables($rawHtml, $section['variables'] ?? []);

            // Replace slots
            $replaced = $this->replaceSlots($replaced, $context);

            $html .= "\n<!-- Section: {$sectionType} -->\n".$replaced."\n";
        }

        return $html;
    }

    /**
     * Resolve CSS variables from theme or fallbacks.
     */
    protected function resolveCssVariables(BusinessSite $site, ?SiteThemeVersion $themeVersion): array
    {
        $defaults = [
            '--fb-primary' => '#2563eb',
            '--fb-accent' => '#3b82f6',
            '--fb-bg' => '#ffffff',
            '--fb-surface' => '#f8fafc',
            '--fb-text' => '#0f172a',
            '--fb-font-heading' => 'inherit',
            '--fb-font-body' => 'inherit',
            '--fb-radius' => '0.5rem',
        ];

        if ($themeVersion && ! empty($themeVersion->theme['css_variables'])) {
            return array_merge($defaults, $themeVersion->theme['css_variables']);
        }

        return $defaults;
    }

    protected function generateCssBlock(array $vars): string
    {
        $cssLines = [];
        foreach ($vars as $key => $val) {
            $cssLines[] = "        {$key}: {$val};";
        }

        return "<style>\n    :root {\n".implode("\n", $cssLines)."\n    }\n</style>";
    }

    protected function renderOptionalCssLink(?string $cssPath): string
    {
        if (! $cssPath) {
            return '';
        }

        return '<link rel="stylesheet" href="'.e($cssPath).'">';
    }

    /**
     * Resolve header and footer shell.
     */
    protected function resolveShell(BusinessSite $site, ?SiteThemeVersion $themeVersion, array $context): array
    {
        $shellHtml = $themeVersion?->theme['shell_html'] ?? $this->getDefaultShellHtml($site);

        // Separate header and footer
        $header = '';
        $footer = '';

        if (preg_match('/<header[^>]*data-fb-shell="header"[^>]*>.*?<\/header>/is', $shellHtml, $headerMatch)) {
            $header = $headerMatch[0];
        } else {
            $header = $this->getDefaultHeaderHtml($site);
        }

        if (preg_match('/<footer[^>]*data-fb-shell="footer"[^>]*>.*?<\/footer>/is', $shellHtml, $footerMatch)) {
            $footer = $footerMatch[0];
        } else {
            $footer = $this->getDefaultFooterHtml($site);
        }

        return [
            'header' => $this->replaceSlots($header, $context),
            'footer' => $this->replaceSlots($footer, $context),
        ];
    }

    /**
     * Replace data-fb-slot="{slotName}" with native Blade component output.
     */
    public function replaceSlots(string $html, array $context): string
    {
        $allowedSlots = [
            'logo',
            'nav',
            'products',
            'services',
            'lead-form',
            'whatsapp-button',
            'cart-button',
            'map',
        ];

        return preg_replace_callback(
            '/<([a-zA-Z0-9]+)[^>]*data-fb-slot="([a-zA-Z0-9_-]+)"[^>]*>.*?<\/\1>|<([a-zA-Z0-9]+)[^>]*data-fb-slot="([a-zA-Z0-9_-]+)"[^>]*\/>/is',
            function ($matches) use ($allowedSlots, $context) {
                $slotName = ! empty($matches[2]) ? $matches[2] : (! empty($matches[4]) ? $matches[4] : '');

                if (! in_array($slotName, $allowedSlots, true)) {
                    return '';
                }

                return $this->renderSlot($slotName, $context);
            },
            $html
        );
    }

    /**
     * Render a specific slot view.
     */
    public function renderSlot(string $slotName, array $context): string
    {
        $viewName = "storefront.slots.{$slotName}";

        if (View::exists($viewName)) {
            return View::make($viewName, $context)->render();
        }

        return '';
    }

    /**
     * Replace data-fb-text, data-fb-image, data-fb-link inside a section.
     */
    public function replaceVariables(string $html, array $variables): string
    {
        // Replace data-fb-text="{key}" inner content
        $html = preg_replace_callback(
            '/(<[a-zA-Z0-9]+[^>]*data-fb-text="([^"]+)"[^>]*>)(.*?)(<\/[a-zA-Z0-9]+>)/is',
            function ($matches) use ($variables) {
                $key = $matches[2];
                $val = $variables[$key] ?? $matches[3];

                return $matches[1].e($val).$matches[4];
            },
            $html
        );

        // Replace data-fb-image="{key}" src attribute
        $html = preg_replace_callback(
            '/(<img[^>]*data-fb-image="([^"]+)"[^>]*src=")([^"]*)(")/is',
            function ($matches) use ($variables) {
                $key = $matches[2];
                $val = $variables[$key] ?? $matches[3];

                return $matches[1].e($val).$matches[4];
            },
            $html
        );

        // Replace data-fb-link="{key}" href attribute
        $html = preg_replace_callback(
            '/(<a[^>]*data-fb-link="([^"]+)"[^>]*href=")([^"]*)(")/is',
            function ($matches) use ($variables) {
                $key = $matches[2];
                $val = $variables[$key] ?? $matches[3];

                return $matches[1].e($val).$matches[4];
            },
            $html
        );

        return $html;
    }

    /**
     * Fallback shell HTML.
     */
    protected function getDefaultShellHtml(BusinessSite $site): string
    {
        return $this->getDefaultHeaderHtml($site)."\n".$this->getDefaultFooterHtml($site);
    }

    protected function getDefaultHeaderHtml(BusinessSite $site): string
    {
        return <<<'HTML'
<header data-fb-shell="header" class="border-b border-slate-200/80 bg-[var(--fb-bg)] sticky top-0 z-40 backdrop-blur-md bg-opacity-95">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
        <div data-fb-slot="logo"></div>
        <div class="hidden md:flex items-center" data-fb-slot="nav"></div>
        <div class="flex items-center gap-3">
            <div data-fb-slot="whatsapp-button"></div>
            <div data-fb-slot="cart-button"></div>
        </div>
    </div>
</header>
HTML;
    }

    protected function getDefaultFooterHtml(BusinessSite $site): string
    {
        $siteName = $site->profile['name'] ?? $site->tenant->name ?? 'Toko';
        $year = date('Y');

        return <<<HTML
<footer data-fb-shell="footer" class="border-t border-slate-200 bg-[var(--fb-surface)] py-12 mt-16 text-sm text-slate-500">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-6">
        <div>
            <p>&copy; {$year} {$siteName}. Seluruh hak cipta dilindungi.</p>
        </div>
        <div class="flex items-center gap-6 text-xs">
            <a href="https://fabriku.id" target="_blank" rel="noopener" class="hover:text-[var(--fb-primary)] transition">
                Dibuat dengan Fabriku
            </a>
            <span class="text-slate-300">&bull;</span>
            <a href="/lapor" class="text-slate-400 hover:text-slate-600 transition">
                Laporkan situs
            </a>
        </div>
    </div>
</footer>
HTML;
    }

    /**
     * Fallback sections when no custom sections exist yet.
     */
    protected function getDefaultSections(BusinessSite $site): array
    {
        $mode = $site->mode ?? 'produk';
        $siteName = $site->profile['name'] ?? $site->tenant->name ?? 'Usaha Kami';
        $headline = $site->profile['description'] ?? 'Selamat Datang di Katalog Resmi Kami';

        $sections = [];

        // 1. Hero
        $sections[] = [
            'id' => 'hero-1',
            'type' => 'hero',
            'sort' => 10,
            'is_visible' => true,
            'variables' => [
                'hero-title' => $siteName,
                'hero-subtitle' => $headline,
            ],
            'html' => <<<HTML
<section data-fb-section="hero" class="py-16 md:py-24 bg-gradient-to-b from-[var(--fb-surface)] to-[var(--fb-bg)] border-b border-slate-100">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 text-center">
        <h1 data-fb-text="hero-title" class="text-3xl md:text-5xl font-extrabold tracking-tight text-[var(--fb-text)] mb-4">{$siteName}</h1>
        <p data-fb-text="hero-subtitle" class="text-base md:text-xl text-slate-600 max-w-2xl mx-auto mb-8">{$headline}</p>
        <div class="flex flex-wrap items-center justify-center gap-4">
            <div data-fb-slot="whatsapp-button"></div>
        </div>
    </div>
</section>
HTML
        ];

        // 2. Products (for produk or gabungan)
        if ($mode === 'produk' || $mode === 'gabungan') {
            $sections[] = [
                'id' => 'featured-products-1',
                'type' => 'featured-products',
                'sort' => 20,
                'is_visible' => true,
                'html' => <<<'HTML'
<section data-fb-section="featured-products" class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-[var(--fb-text)]">Katalog Produk</h2>
            <p class="text-sm text-slate-500 mt-1">Pilihan produk berkualitas langsung dari kami.</p>
        </div>
        <a href="/produk" class="text-sm font-semibold text-[var(--fb-primary)] hover:underline">Lihat Semua &rarr;</a>
    </div>
    <div data-fb-slot="products"></div>
</section>
HTML
            ];
        }

        // 3. Services (for jasa or gabungan)
        if ($mode === 'jasa' || $mode === 'gabungan') {
            $sections[] = [
                'id' => 'service-list-1',
                'type' => 'service-list',
                'sort' => 30,
                'is_visible' => true,
                'html' => <<<'HTML'
<section data-fb-section="service-list" class="py-16 bg-[var(--fb-surface)] border-y border-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <h2 class="text-2xl font-bold tracking-tight text-[var(--fb-text)]">Layanan Kami</h2>
            <p class="text-sm text-slate-500 mt-2">Solusi pengerjaan profesional sesuai kebutuhan Anda.</p>
        </div>
        <div data-fb-slot="services"></div>
    </div>
</section>
HTML
            ];

            $sections[] = [
                'id' => 'lead-form-1',
                'type' => 'contact',
                'sort' => 40,
                'is_visible' => true,
                'html' => <<<'HTML'
<section data-fb-section="contact" class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div data-fb-slot="lead-form"></div>
</section>
HTML
            ];
        }

        // 4. Contact / Map
        $sections[] = [
            'id' => 'contact-1',
            'type' => 'contact',
            'sort' => 50,
            'is_visible' => true,
            'html' => <<<'HTML'
<section id="contact" data-fb-section="contact" class="py-12 bg-slate-50 border-t border-slate-100">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">
        <h3 class="text-lg font-bold text-[var(--fb-text)] mb-4 text-center">Informasi Kontak & Lokasi</h3>
        <div data-fb-slot="map"></div>
    </div>
</section>
HTML
        ];

        return $sections;
    }
}
