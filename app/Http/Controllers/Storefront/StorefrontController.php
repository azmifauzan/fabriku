<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\BusinessSite;
use App\Models\Tenant;
use App\Services\Storefront\Storefront;
use App\Services\Storefront\ThemeRenderer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StorefrontController extends Controller
{
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

        $products = Storefront::for($tenant)->products()->get();
        $services = Storefront::for($tenant)->services()->get();

        $html = $renderer->render($site, $site->activeThemeVersion, [
            'page' => 'home',
            'products' => $products,
            'services' => $services,
            'title' => $site->profile['name'] ?? $tenant->name ?? 'Toko Kami',
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
        $tenant = $this->getTenant();

        $siteName = $site->profile['name'] ?? $tenant->name ?? 'Toko';

        $pageContent = view('storefront.pages.cart', [
            'site' => $site,
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
