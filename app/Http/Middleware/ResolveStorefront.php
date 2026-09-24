<?php

namespace App\Http\Middleware;

use App\Models\BusinessSite;
use App\Services\Storefront\Storefront;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveStorefront
{
    /**
     * Handle an incoming request and resolve storefront host to a BusinessSite.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = strtolower(trim(explode(':', $request->getHost())[0]));
        $storefrontDomain = strtolower(config('app.storefront_domain', 'fabriku.biz.id'));
        $mainDomain = strtolower(config('app.main_domain', 'fabriku.id'));

        // 1. Apex domain redirect: fabriku.biz.id & www.fabriku.biz.id -> 301 to fabriku.id
        if ($host === $storefrontDomain || $host === 'www.'.$storefrontDomain) {
            return redirect()->away('https://'.$mainDomain, 301);
        }

        // 2. Extract subdomain or match custom domain
        $slug = null;
        if (str_ends_with($host, '.'.$storefrontDomain)) {
            $slug = substr($host, 0, -strlen('.'.$storefrontDomain));
        } elseif (str_ends_with($host, '.localhost')) {
            $slug = substr($host, 0, -strlen('.localhost'));
        } elseif ($request->header('X-Storefront-Slug')) {
            $slug = $request->header('X-Storefront-Slug');
        }

        $site = null;
        if ($slug !== null) {
            if (BusinessSite::isReservedSlug($slug)) {
                abort(404, 'Toko tidak ditemukan.');
            }

            $site = BusinessSite::where('slug', $slug)->first();
        } else {
            // Check active custom domain
            $site = BusinessSite::where('custom_domain', $host)
                ->where('domain_status', 'active')
                ->first();
        }

        // 3. Check site existence
        if (! $site) {
            abort(404, 'Toko tidak ditemukan.');
        }

        // 4. Check suspension
        if ($site->isSuspended()) {
            abort(403, 'Situs ini telah dinonaktifkan.');
        }

        // 5. Check tenant existence
        $tenant = $site->tenant;
        if (! $tenant) {
            abort(404, 'Toko tidak ditemukan.');
        }

        // 6. Bind to Storefront helper and request attributes
        Storefront::bind($site, $tenant);
        $request->attributes->set('storefront_site', $site);
        $request->attributes->set('storefront_tenant', $tenant);

        // Remove storefront_host from route parameters so controller methods only receive path parameters
        $request->route()?->forgetParameter('storefront_host');

        $response = $next($request);

        // 7. Public cache header (60 seconds) for successful GET responses
        if ($request->isMethod('GET') && $response->isSuccessful()) {
            $response->headers->set('Cache-Control', 'public, max-age=60');
        }

        return $response;
    }
}
