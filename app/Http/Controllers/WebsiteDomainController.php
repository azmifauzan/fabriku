<?php

namespace App\Http\Controllers;

use App\Models\BusinessSite;
use App\Services\Storefront\CloudflareDomains;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WebsiteDomainController extends Controller
{
    public function save(Request $request, CloudflareDomains $cloudflare): RedirectResponse
    {
        $site = $this->site($request);
        $data = $request->validate([
            'domain' => ['required', 'string', 'max:253', 'regex:/^(?=.{4,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/', Rule::unique('business_sites', 'custom_domain')->ignore($site->id)],
        ]);
        $domain = strtolower($data['domain']);
        foreach ([config('app.storefront_domain'), config('app.main_domain')] as $reservedDomain) {
            abort_if($domain === $reservedDomain || str_ends_with($domain, '.'.$reservedDomain), 422);
        }
        if ($site->custom_domain && $site->custom_domain !== $domain) {
            return back()->withErrors(['domain' => 'Lepas domain lama lewat bantuan Fabriku sebelum menggantinya.']);
        }
        if ($site->cloudflare_hostname_id) {
            return back()->with('success', 'Domain sedang diverifikasi.');
        }
        try {
            $result = $cloudflare->create($domain);
        } catch (\Throwable $e) {
            return back()->withErrors(['domain' => 'Cloudflare: '.$e->getMessage()]);
        }
        $site->update(['custom_domain' => $domain, 'cloudflare_hostname_id' => $result['id'], 'domain_status' => 'pending']);

        return back()->with('success', 'Domain didaftarkan. Pasang record DNS sesuai petunjuk, lalu periksa statusnya.');
    }

    public function sync(Request $request, CloudflareDomains $cloudflare): RedirectResponse
    {
        $site = $this->site($request);
        abort_unless($site->cloudflare_hostname_id && $site->custom_domain, 404);
        try {
            $result = $cloudflare->details($site->cloudflare_hostname_id);
        } catch (\Throwable $e) {
            return back()->withErrors(['domain' => 'Cloudflare: '.$e->getMessage()]);
        }
        $ready = $cloudflare->isReady($result) && $cloudflare->dnsPointsToTarget($site->custom_domain);
        $site->update(['domain_status' => $ready ? 'active' : 'pending']);

        return back()->with($ready ? 'success' : 'warning', $ready ? 'Domain dan HTTPS sudah aktif.' : 'Domain belum siap. Pastikan CNAME, validasi domain, dan sertifikat sudah aktif.');
    }

    private function site(Request $request): BusinessSite
    {
        abort_unless($request->user()->isManager(), 403);

        return BusinessSite::where('tenant_id', $request->user()->tenant_id)->firstOrFail();
    }
}
