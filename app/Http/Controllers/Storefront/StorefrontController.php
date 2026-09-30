<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Jobs\NotifyStorefrontRequest;
use App\Models\BusinessSite;
use App\Models\Lead;
use App\Models\Scopes\TenantScope;
use App\Models\SiteContentPage;
use App\Models\SiteReport;
use App\Models\Tenant;
use App\Services\Storefront\Storefront;
use App\Services\Storefront\ThemeRenderer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class StorefrontController extends Controller
{
    private const LEAD_CONSENT_TEXT = 'Saya bersedia dihubungi oleh pihak toko terkait penawaran layanan ini sesuai dengan UU Perlindungan Data Pribadi (UU No. 27/2022).';

    protected function getSite(): BusinessSite
    {
        $site = Storefront::currentSite();
        if (! $site) {
            abort(404, 'Toko tidak ditemukan.');
        }

        return $site;
    }

    protected function getTenant(): Tenant
    {
        $tenant = Storefront::currentTenant();
        if (! $tenant) {
            abort(404, 'Tenant tidak ditemukan.');
        }

        return $tenant;
    }

    public function home(Request $request, ThemeRenderer $renderer): Response
    {
        $site = $this->getSite();
        $tenant = $this->getTenant();

        $products = $site->mode === 'jasa' ? collect() : Storefront::for($tenant)->products()->get();
        $services = $site->mode === 'produk' ? collect() : Storefront::for($tenant)->services()->get();

        $html = $renderer->render($site, $site->activeThemeVersion, [
            'page' => 'home',
            'products' => $products,
            'services' => $services,
            'title' => $site->seo['title'] ?? $site->profile['name'] ?? $tenant->name ?? 'Toko Kami',
        ]);

        return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
    }

    public function contentPage(string $slug, ThemeRenderer $renderer): Response
    {
        $site = $this->getSite();
        $page = SiteContentPage::query()
            ->where('business_site_id', $site->id)
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $html = $renderer->render($site, $site->activeThemeVersion, [
            'page' => 'content_page',
            'page_content' => $page->html,
            'page_css' => $page->css,
            'content_page' => $page,
            'title' => $page->title,
            'seo_title' => $page->seo_title ?: $page->title,
            'seo_description' => $page->seo_description ?: ($site->seo['description'] ?? $site->profile['description'] ?? ''),
        ]);

        return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
    }

    public function products(Request $request, ThemeRenderer $renderer): Response
    {
        $site = $this->getSite();
        $tenant = $this->getTenant();

        if ($site->mode === 'jasa') {
            abort(404);
        }

        $products = Storefront::for($tenant)->products()->get();
        $siteName = $site->profile['name'] ?? $tenant->name ?? 'Toko';

        $pageContent = view('storefront.pages.products', [
            'products' => $products,
            'site' => $site,
        ])->render();

        $html = $renderer->render($site, $site->activeThemeVersion, [
            'page' => 'products',
            'page_content' => $pageContent,
            'products' => $products,
            'title' => "Semua Produk - {$siteName}",
        ]);

        return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
    }

    public function productDetail(Request $request, ThemeRenderer $renderer, string $slug): Response
    {
        $site = $this->getSite();
        if ($site->mode === 'jasa') {
            abort(404);
        }
        $tenant = $this->getTenant();

        $product = Storefront::for($tenant)->products()->where('slug', $slug)->firstOrFail();
        $siteName = $site->profile['name'] ?? $tenant->name ?? 'Toko';

        $pageContent = view('storefront.pages.product-detail', [
            'product' => $product,
            'site' => $site,
        ])->render();

        $html = $renderer->render($site, $site->activeThemeVersion, [
            'page' => 'product_detail',
            'page_content' => $pageContent,
            'product' => $product,
            'title' => "{$product->title} - {$siteName}",
        ]);

        return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
    }

    public function services(Request $request, ThemeRenderer $renderer): Response
    {
        $site = $this->getSite();
        $tenant = $this->getTenant();

        if ($site->mode === 'produk') {
            abort(404);
        }

        $services = Storefront::for($tenant)->services()->get();
        $siteName = $site->profile['name'] ?? $tenant->name ?? 'Layanan';

        $pageContent = view('storefront.pages.services', [
            'services' => $services,
            'site' => $site,
        ])->render();

        $html = $renderer->render($site, $site->activeThemeVersion, [
            'page' => 'services',
            'page_content' => $pageContent,
            'services' => $services,
            'title' => "Semua Layanan - {$siteName}",
        ]);

        return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
    }

    public function serviceDetail(Request $request, ThemeRenderer $renderer, string $slug): Response
    {
        $site = $this->getSite();
        if ($site->mode === 'produk') {
            abort(404);
        }
        $tenant = $this->getTenant();

        $service = Storefront::for($tenant)->services()->where('slug', $slug)->firstOrFail();
        $siteName = $site->profile['name'] ?? $tenant->name ?? 'Layanan';

        $pageContent = view('storefront.pages.service-detail', [
            'service' => $service,
            'site' => $site,
        ])->render();

        $html = $renderer->render($site, $site->activeThemeVersion, [
            'page' => 'service_detail',
            'page_content' => $pageContent,
            'service' => $service,
            'title' => "{$service->name} - {$siteName}",
        ]);

        return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
    }

    public function cart(Request $request, ThemeRenderer $renderer): Response
    {
        $site = $this->getSite();
        if ($site->mode === 'jasa') {
            abort(404);
        }
        $tenant = $this->getTenant();

        $siteName = $site->profile['name'] ?? $tenant->name ?? 'Toko';

        $pageContent = view('storefront.pages.cart', [
            'site' => $site,
            'lines' => CartController::lines($request, $tenant->id),
        ])->render();

        $html = $renderer->render($site, $site->activeThemeVersion, [
            'page' => 'cart',
            'page_content' => $pageContent,
            'title' => "Keranjang - {$siteName}",
        ]);

        return response($html)
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    public function submitLead(Request $request): RedirectResponse
    {
        $site = $this->getSite();
        $tenant = $this->getTenant();
        abort_unless($site->mode !== 'produk' && $site->canAcceptOrders(), 404);

        if ($request->filled('_hp_site_lead')) {
            return back()->with('success', 'Permintaan Anda terkirim. Tim toko akan menghubungi Anda.');
        }

        $data = $request->validate([
            'idempotency_key' => ['required', 'uuid'],
            'service_id' => [
                'nullable',
                'integer',
                Rule::exists('services', 'id')
                    ->where('tenant_id', $tenant->id)
                    ->where('is_public', true)
                    ->where('is_active', true),
            ],
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:32', 'regex:/^[0-9+().\-\s]{7,32}$/'],
            'message' => ['nullable', 'string', 'max:2000'],
            'consent' => ['required', 'accepted'],
        ]);

        $lead = Lead::withoutGlobalScope(TenantScope::class)->firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'idempotency_key' => $data['idempotency_key'],
            ],
            [
                'business_site_id' => $site->id,
                'service_id' => $data['service_id'] ?? null,
                'source' => 'website',
                'name' => $data['name'],
                'phone' => $data['phone'],
                'message' => $data['message'] ?? null,
                'consent_at' => now(),
                'consent_text' => self::LEAD_CONSENT_TEXT,
                'status' => 'new',
            ]
        );
        if ($lead->wasRecentlyCreated) {
            NotifyStorefrontRequest::dispatch($site->id, 'lead', $lead->id)->afterCommit();
        }

        return back()->with('success', 'Permintaan Anda terkirim. Tim toko akan menghubungi Anda melalui WhatsApp.');
    }

    public function privacy(ThemeRenderer $renderer): Response
    {
        $site = $this->getSite();
        $html = $renderer->render($site, $site->activeThemeVersion, [
            'page' => 'privacy',
            'page_content' => view('storefront.pages.privacy', ['site' => $site])->render(),
            'title' => 'Privasi - '.($site->profile['name'] ?? 'Website Usaha'),
        ]);

        return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
    }

    public function report(ThemeRenderer $renderer): Response
    {
        $site = $this->getSite();
        $html = $renderer->render($site, $site->activeThemeVersion, [
            'page' => 'report',
            'page_content' => view('storefront.pages.report')->render(),
            'title' => 'Laporkan situs',
        ]);

        return response($html)->header('Content-Type', 'text/html; charset=UTF-8')->header('X-Robots-Tag', 'noindex, nofollow');
    }

    public function submitReport(Request $request): RedirectResponse
    {
        $site = $this->getSite();
        if ($request->filled('_hp_site_report')) {
            return back()->with('success', 'Laporan diterima.');
        }
        $data = $request->validate([
            'category' => ['required', Rule::in(['penipuan', 'barang_terlarang', 'privasi', 'lainnya'])],
            'details' => ['required', 'string', 'min:10', 'max:3000'],
            'contact_email' => ['nullable', 'email', 'max:255'],
        ]);
        SiteReport::create(['business_site_id' => $site->id, 'tenant_id' => $site->tenant_id, ...$data]);

        return back()->with('success', 'Laporan diterima. Tim Fabriku akan meninjaunya.');
    }

    public function robots(Request $request): Response
    {
        $site = $this->getSite();

        if (! $site->isPublished()) {
            return response("User-agent: *\nDisallow: /", 200, ['Content-Type' => 'text/plain']);
        }

        $sitemapUrl = $site->getStorefrontUrl().'/sitemap.xml';

        $content = "User-agent: *\nAllow: /\nDisallow: /keranjang\nDisallow: /terima-kasih\n\nSitemap: {$sitemapUrl}\n";

        return response($content, 200, ['Content-Type' => 'text/plain']);
    }

    public function sitemap(Request $request): Response
    {
        $site = $this->getSite();
        $tenant = $this->getTenant();

        if (! $site->isPublished()) {
            abort(404);
        }

        $baseUrl = rtrim($site->getStorefrontUrl(), '/');
        $products = Storefront::for($tenant)->products()->get();
        $services = Storefront::for($tenant)->services()->get();
        $contentPages = $site->contentPages()->where('is_published', true)->get();

        $urls = [];
        $urls[] = ['loc' => $baseUrl.'/', 'priority' => '1.0'];

        if ($site->mode !== 'jasa') {
            $urls[] = ['loc' => $baseUrl.'/produk', 'priority' => '0.8'];
            foreach ($products as $p) {
                $urls[] = ['loc' => $baseUrl."/produk/{$p->slug}", 'priority' => '0.7'];
            }
        }

        if ($site->mode !== 'produk') {
            $urls[] = ['loc' => $baseUrl.'/layanan', 'priority' => '0.8'];
            foreach ($services as $s) {
                if ($s->slug) {
                    $urls[] = ['loc' => $baseUrl."/layanan/{$s->slug}", 'priority' => '0.7'];
                }
            }
        }

        foreach ($contentPages as $page) {
            $urls[] = ['loc' => $baseUrl.'/halaman/'.$page->slug, 'priority' => '0.6'];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($urls as $u) {
            $xml .= '<url>';
            $xml .= '<loc>'.e($u['loc']).'</loc>';
            $xml .= '<priority>'.$u['priority'].'</priority>';
            $xml .= '</url>';
        }
        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
