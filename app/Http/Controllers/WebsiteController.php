<?php

namespace App\Http\Controllers;

use App\Models\BusinessSite;
use App\Models\InventoryItem;
use App\Models\Lead;
use App\Models\SalesOrder;
use App\Models\Scopes\TenantScope;
use App\Models\Service;
use App\Models\SiteProduct;
use App\Models\SiteThemeVersion;
use App\Models\User;
use App\Services\Storefront\CloudflareDomains;
use App\Services\Storefront\ThemeRenderer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class WebsiteController extends Controller
{
    public function index(Request $request, CloudflareDomains $cloudflare): Response
    {
        $tenantId = $request->user()->tenant_id;
        $site = BusinessSite::where('tenant_id', $tenantId)->with(['activeThemeVersion', 'themeVersions' => fn ($q) => $q->latest('version'), 'recipients:id,name,email'])->first();
        $canManage = $request->user()->isManager();
        abort_unless($canManage || ($site && $site->recipients->contains($request->user()->id)), 403);
        $domainRecords = [];
        if ($canManage && $site?->cloudflare_hostname_id) {
            try {
                $details = $cloudflare->details($site->cloudflare_hostname_id);
                if ($ownership = ($details['ownership_verification'] ?? null)) {
                    $domainRecords[] = ['type' => 'TXT', 'name' => $ownership['name'] ?? '', 'value' => $ownership['value'] ?? ''];
                }
                foreach ($details['ssl']['validation_records'] ?? [] as $record) {
                    if (! empty($record['txt_name']) && ! empty($record['txt_value'])) {
                        $domainRecords[] = ['type' => 'TXT', 'name' => $record['txt_name'], 'value' => $record['txt_value']];
                    }
                }
            } catch (\Throwable) {
                // The page remains available when Cloudflare is temporarily unavailable.
            }
        }

        return Inertia::render('Website/Index', [
            'site' => $site,
            'domainRecords' => $domainRecords,
            'cnameTarget' => $cloudflare->cnameTarget(),
            'canManage' => $canManage,
            'catalog' => $canManage ? InventoryItem::query()->whereNotNull('product_code')->where('product_code', '!=', '')
                ->select('product_code')->selectRaw('MIN(product_name) as product_name')->groupBy('product_code')->orderBy('product_name')->get() : [],
            'products' => $canManage ? SiteProduct::orderBy('sort')->get() : [],
            'services' => $canManage ? Service::orderBy('name')->get(['id', 'name', 'slug', 'is_public', 'is_active']) : [],
            'users' => $canManage ? User::withoutGlobalScope(TenantScope::class)->where('tenant_id', $tenantId)->where('is_active', true)->get(['id', 'name', 'email']) : [],
            'leads' => $site ? Lead::where('business_site_id', $site->id)->with('service:id,name')->latest()->limit(50)->get() : [],
            'orders' => $site ? SalesOrder::whereNotNull('storefront_checkout_key')->with('customer:id,name,phone')->latest()->limit(50)->get(['id', 'customer_id', 'order_number', 'status', 'payment_status', 'total_amount', 'created_at']) : [],
        ]);
    }

    public function save(Request $request): RedirectResponse
    {
        $this->ensureManager($request);
        $tenant = $request->user()->tenant;
        abort_unless($tenant->hasFeature('business_site'), 403);
        $site = BusinessSite::where('tenant_id', $tenant->id)->first();
        $data = $request->validate([
            'slug' => ['required', 'regex:/^[a-z0-9](?:[a-z0-9-]{1,48}[a-z0-9])$/', 'min:3', 'max:50', Rule::unique('business_sites')->ignore($site?->id), function ($attribute, $value, $fail) {
                if (BusinessSite::isReservedSlug($value)) {
                    $fail('Alamat situs ini tidak tersedia.');
                }
            }],
            'mode' => ['required', Rule::in(['produk', 'jasa', 'gabungan'])],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:300'],
            'whatsapp' => ['nullable', 'string', 'max:32', 'regex:/^[0-9+().\-\s]*$/'],
            'address' => ['nullable', 'string', 'max:500'],
            'seo_title' => ['nullable', 'string', 'max:70'],
            'seo_description' => ['nullable', 'string', 'max:160'],
        ]);
        $profile = ['name' => $data['name'], 'description' => $data['description'] ?? null, 'whatsapp' => $data['whatsapp'] ?? null, 'address' => $data['address'] ?? null];
        $site ??= new BusinessSite(['tenant_id' => $tenant->id, 'status' => 'draft']);
        $modeChanged = $site->exists && $site->mode !== $data['mode'];
        $site->fill(['slug' => strtolower($data['slug']), 'mode' => $data['mode'], 'profile' => $profile,
            'seo' => ['title' => $data['seo_title'] ?? null, 'description' => $data['seo_description'] ?? null],
        ]);
        if ($modeChanged) {
            $site->active_theme_version_id = null;
        }
        $site->save();

        return back()->with('success', 'Pengaturan website disimpan.');
    }

    public function theme(Request $request, ThemeRenderer $renderer): RedirectResponse
    {
        $this->ensureManager($request);
        $site = $this->site($request);
        $data = $request->validate([
            'primary' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'accent' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'background' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'text' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'hero_title' => ['required', 'string', 'max:120'],
            'hero_subtitle' => ['nullable', 'string', 'max:300'],
            'preset' => ['required', Rule::in(['ruang', 'etalase', 'studio'])],
        ]);
        $currentPreset = $site->activeThemeVersion?->theme['preset'] ?? 'ruang';
        $sections = $currentPreset === $data['preset']
            ? ($site->activeThemeVersion?->sections ?: $renderer->sectionsForPreset($site, $data['preset']))
            : $renderer->sectionsForPreset($site, $data['preset']);
        foreach ($sections as &$section) {
            if (($section['type'] ?? '') === 'hero') {
                $section['variables']['hero-title'] = $data['hero_title'];
                $section['variables']['hero-subtitle'] = $data['hero_subtitle'] ?? '';
            }
        }
        unset($section);
        DB::transaction(function () use ($site, $request, $sections, $data) {
            $version = SiteThemeVersion::create([
                'tenant_id' => $site->tenant_id,
                'business_site_id' => $site->id,
                'source' => 'manual',
                'version' => ($site->themeVersions()->max('version') ?? 0) + 1,
                'theme' => ['preset' => $data['preset'], 'css_variables' => [
                    '--fb-primary' => $data['primary'], '--fb-accent' => $data['accent'],
                    '--fb-bg' => $data['background'], '--fb-text' => $data['text'],
                ]],
                'sections' => $sections,
                'created_by' => $request->user()->id,
            ]);
            $site->update(['active_theme_version_id' => $version->id]);
        });

        return back()->with('success', 'Desain website disimpan sebagai versi baru.');
    }

    public function restoreTheme(Request $request, SiteThemeVersion $version): RedirectResponse
    {
        $this->ensureManager($request);
        $site = $this->site($request);
        abort_unless($version->business_site_id === $site->id && $version->tenant_id === $site->tenant_id, 404);
        $site->update(['active_theme_version_id' => $version->id]);

        return back()->with('success', 'Versi desain dipulihkan.');
    }

    public function publish(Request $request): RedirectResponse
    {
        $this->ensureManager($request);
        $site = $this->site($request);
        abort_if($site->isSuspended(), 403);
        $data = $request->validate(['status' => ['required', Rule::in(['published', 'paused'])]]);
        $site->update(['status' => $data['status'], 'published_at' => $data['status'] === 'published' ? ($site->published_at ?? now()) : $site->published_at]);

        return back()->with('success', $data['status'] === 'published' ? 'Website sudah terbit.' : 'Website dijeda.');
    }

    public function completeSetup(Request $request): RedirectResponse
    {
        $this->ensureManager($request);
        $this->site($request)->update(['setup_completed_at' => now()]);

        return back()->with('success', 'Pengaturan awal website selesai.');
    }

    public function product(Request $request): RedirectResponse
    {
        $this->ensureManager($request);
        $site = $this->site($request);
        $data = $request->validate([
            'product_code' => ['required', 'string', 'max:255', Rule::exists('inventory_items', 'product_code')->where('tenant_id', $site->tenant_id)],
            'slug' => ['required', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'max:100', Rule::unique('site_products')->where('tenant_id', $site->tenant_id)->ignore(SiteProduct::where('product_code', $request->input('product_code'))->first()?->id)],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_visible' => ['required', 'boolean'],
        ]);
        SiteProduct::updateOrCreate(['tenant_id' => $site->tenant_id, 'product_code' => $data['product_code']], [
            'slug' => strtolower($data['slug']), 'title' => $data['title'], 'description' => $data['description'] ?? null, 'is_visible' => $data['is_visible'],
        ]);

        return back()->with('success', 'Produk website disimpan.');
    }

    public function service(Request $request, Service $service): RedirectResponse
    {
        $this->ensureManager($request);
        $site = $this->site($request);
        abort_unless($service->tenant_id === $site->tenant_id, 404);
        $data = $request->validate([
            'is_public' => ['required', 'boolean'],
            'slug' => ['required', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'max:100', Rule::unique('services')->where('tenant_id', $site->tenant_id)->ignore($service->id)],
        ]);
        $service->update($data);

        return back()->with('success', 'Tampilan layanan diperbarui.');
    }

    public function recipients(Request $request): RedirectResponse
    {
        $this->ensureManager($request);
        $site = $this->site($request);
        $data = $request->validate(['user_ids' => ['array'], 'user_ids.*' => ['integer', Rule::exists('users', 'id')->where('tenant_id', $site->tenant_id)->where('is_active', true)]]);
        $site->recipients()->sync($data['user_ids'] ?? []);

        return back()->with('success', 'Penerima notifikasi diperbarui.');
    }

    private function site(Request $request): BusinessSite
    {
        return BusinessSite::where('tenant_id', $request->user()->tenant_id)->firstOrFail();
    }

    private function ensureManager(Request $request): void
    {
        abort_unless($request->user()->isManager(), 403);
    }
}
