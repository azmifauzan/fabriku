<?php

namespace App\Http\Controllers;

use App\Models\BusinessSite;
use App\Models\InventoryItem;
use App\Models\Lead;
use App\Models\SalesOrder;
use App\Models\Scopes\TenantScope;
use App\Models\Service;
use App\Models\SiteContentPage;
use App\Models\SiteProduct;
use App\Models\SiteThemeVersion;
use App\Models\User;
use App\Services\SatsetuiTemplateClient;
use App\Services\Storefront\CloudflareDomains;
use App\Services\Storefront\HtmlSanitizerService;
use App\Services\Storefront\ThemeRenderer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;
use Throwable;

class WebsiteController extends Controller
{
    public function index(Request $request, CloudflareDomains $cloudflare): Response
    {
        $tenantId = $request->user()->tenant_id;
        $site = BusinessSite::where('tenant_id', $tenantId)->with([
            'activeThemeVersion',
            'themeVersions' => fn ($query) => $query->select(['id', 'tenant_id', 'business_site_id', 'version', 'source', 'created_at'])->latest('version'),
            'recipients:id,name,email',
        ])->first();
        $draftVersion = $site?->draftThemeVersion()->first(['id', 'version', 'source', 'theme']);
        if ($site?->activeThemeVersion) {
            $activeVersion = $site->activeThemeVersion;
            $activeVersion->setAttribute('theme', array_intersect_key($activeVersion->theme ?? [], array_flip(['preset', 'css_variables'])));
            $activeVersion->setAttribute('sections', collect($activeVersion->sections ?? [])->map(fn ($section) => [
                'type' => $section['type'] ?? '',
                'variables' => $section['variables'] ?? [],
            ])->all());
        }
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
            } catch (Throwable) {
                // The page remains available when Cloudflare is temporarily unavailable.
            }
        }

        return Inertia::render('Website/Index', [
            'site' => $site,
            'draftThemeVersion' => $draftVersion?->source === 'satsetui' ? [
                'id' => $draftVersion->id,
                'version' => $draftVersion->version,
                'pageCount' => count($draftVersion->theme['satsetui_pages'] ?? []),
            ] : null,
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
            'contentPages' => $canManage && $site ? $site->contentPages()->get(['id', 'business_site_id', 'slug', 'title', 'seo_title', 'seo_description', 'show_in_footer']) : [],
        ]);
    }

    public function satsetuiLaunch(Request $request, SatsetuiTemplateClient $satsetui): RedirectResponse
    {
        $this->ensureManager($request);

        try {
            return redirect()->away($satsetui->launch($request->user(), $this->site($request)));
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors(['satsetui' => 'Satsetui belum dapat dibuka. Coba lagi beberapa saat.']);
        }
    }

    public function satsetuiImport(Request $request, SatsetuiTemplateClient $satsetui, HtmlSanitizerService $sanitizer): RedirectResponse
    {
        $this->ensureManager($request);
        $data = $request->validate(['ticket' => ['required', 'alpha_num', 'min:40', 'max:128']]);
        $site = $this->site($request);

        try {
            $export = $satsetui->export($data['ticket'], $request->user(), $site);
            $template = $export['template'] ?? null;
            $generationId = (int) ($export['generation_id'] ?? 0);
            if (! is_array($template) || ($template['format'] ?? null) !== 'fabriku-site-v1' || $generationId < 1 || ! is_array($template['home'] ?? null)) {
                throw new RuntimeException('Format template Satsetui tidak valid.');
            }

            $homeHtml = $sanitizer->sanitize($this->boundedString($template['home']['html'] ?? null, 500_000));
            if (trim($homeHtml) === '') {
                throw new RuntimeException('Halaman depan dari Satsetui kosong.');
            }

            $pages = $template['pages'] ?? [];
            if (! is_array($pages) || count($pages) > 20) {
                throw new RuntimeException('Daftar halaman dari Satsetui tidak valid.');
            }

            $cssParts = [$this->boundedString($template['home']['css'] ?? '', 100_000)];
            foreach ($pages as $page) {
                if (is_array($page)) {
                    $cssParts[] = $this->boundedString($page['css'] ?? '', 100_000);
                }
            }
            $css = $sanitizer->sanitizeCss(implode("\n", array_filter($cssParts)));
            $homeSeoTitle = $this->nullableBoundedString($template['home']['seo_title'] ?? null, 70);
            $homeSeoDescription = $this->nullableBoundedString($template['home']['seo_description'] ?? null, 160);
            $homeSeo = array_filter([
                'title' => $homeSeoTitle,
                'description' => $homeSeoDescription,
            ], fn ($value) => $value !== null);
            $draftPages = [];
            $seenSlugs = [];
            foreach ($pages as $index => $page) {
                if (! is_array($page)) {
                    throw new RuntimeException('Data halaman Satsetui tidak valid.');
                }

                $title = trim($this->boundedString($page['title'] ?? null, 150));
                $slug = Str::slug($this->boundedString($page['slug'] ?? $title, 100));
                if ($title === '' || $slug === '' || $this->reservedPageSlug($slug) || isset($seenSlugs[$slug])) {
                    throw new RuntimeException('Judul atau alamat halaman Satsetui tidak dapat digunakan.');
                }
                $seenSlugs[$slug] = true;
                $html = $sanitizer->sanitize($this->boundedString($page['html'] ?? null, 500_000));
                if (trim($html) === '') {
                    continue;
                }

                $draftPages[] = [
                    'slug' => $slug,
                    'title' => $title,
                    'seo_title' => $this->nullableBoundedString($page['seo_title'] ?? null, 70),
                    'seo_description' => $this->nullableBoundedString($page['seo_description'] ?? null, 160),
                    'html' => $html,
                    'sort' => $index + 1,
                ];
            }

            DB::transaction(function () use ($site, $request, $generationId, $homeHtml, $css, $draftPages, $homeSeo) {
                $existing = SiteThemeVersion::query()
                    ->where('business_site_id', $site->id)
                    ->where('source', 'satsetui')
                    ->where('satsetui_generation_id', $generationId)
                    ->first();
                if ($existing) {
                    if ((int) $existing->id !== (int) $site->active_theme_version_id) {
                        $site->update(['draft_theme_version_id' => $existing->id]);
                    }

                    return;
                }

                $version = SiteThemeVersion::create([
                    'tenant_id' => $site->tenant_id,
                    'business_site_id' => $site->id,
                    'source' => 'satsetui',
                    'version' => ($site->themeVersions()->max('version') ?? 0) + 1,
                    'theme' => [
                        'preset' => 'ruang',
                        'satsetui_css' => $css,
                        'satsetui_generation_id' => $generationId,
                        'satsetui_home_seo' => $homeSeo,
                        'satsetui_pages' => $draftPages,
                    ],
                    'sections' => [[
                        'type' => 'satsetui-template',
                        'sort' => 0,
                        'is_visible' => true,
                        'variables' => [],
                        'html' => $homeHtml,
                    ]],
                    'satsetui_generation_id' => $generationId,
                    'created_by' => $request->user()->id,
                ]);
                $site->update(['draft_theme_version_id' => $version->id]);
            });

            return redirect()->route('website.index', ['tab' => 'desain'])->with('success', 'Template Satsetui masuk sebagai draf desain. Periksa halaman dan SEO sebelum menerbitkan website.');
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('website.index', ['tab' => 'desain'])
                ->withErrors(['satsetui' => 'Template belum dapat diimpor. Buka kembali hasil di Satsetui dan coba ekspor lagi.']);
        }
    }

    public function publishSatsetuiDraft(Request $request): RedirectResponse
    {
        $this->ensureManager($request);
        $site = $this->site($request);
        $draft = $site->draftThemeVersion;
        abort_unless($draft && $draft->business_site_id === $site->id && $draft->source === 'satsetui', 404);

        $theme = $draft->theme ?? [];
        $pages = $theme['satsetui_pages'] ?? [];
        $homeSeo = $theme['satsetui_home_seo'] ?? [];
        abort_unless(is_array($pages) && is_array($homeSeo), 409);

        DB::transaction(function () use ($site, $draft, $pages, $homeSeo) {
            foreach ($pages as $page) {
                SiteContentPage::updateOrCreate(
                    ['business_site_id' => $site->id, 'slug' => $page['slug']],
                    [
                        'tenant_id' => $site->tenant_id,
                        'title' => $page['title'],
                        'seo_title' => $page['seo_title'],
                        'seo_description' => $page['seo_description'],
                        'html' => $page['html'],
                        'css' => null,
                        'show_in_footer' => true,
                        'is_published' => true,
                        'sort' => $page['sort'],
                    ],
                );
            }

            $site->update([
                'active_theme_version_id' => $draft->id,
                'draft_theme_version_id' => null,
                'seo' => array_merge($site->seo ?? [], $homeSeo),
            ]);
        });

        return back()->with('success', 'Desain dan halaman Satsetui diterapkan. Pengaturan terbit atau jeda website tidak berubah.');
    }

    public function updateContentPage(Request $request, SiteContentPage $page): RedirectResponse
    {
        $this->ensureManager($request);
        $site = $this->site($request);
        abort_unless($page->business_site_id === $site->id && $page->tenant_id === $site->tenant_id, 404);
        $data = $request->validate([
            'seo_title' => ['nullable', 'string', 'max:70'],
            'seo_description' => ['nullable', 'string', 'max:160'],
            'show_in_footer' => ['required', 'boolean'],
        ]);
        $page->update($data);

        return back()->with('success', 'Pengaturan halaman disimpan.');
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
        abort_if($site->activeThemeVersion?->source === 'satsetui' || $site->draftThemeVersion?->source === 'satsetui', 422, 'Desain template ini dikelola dari Satsetui.');
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
        if ($version->source === 'satsetui') {
            $site->update(['draft_theme_version_id' => $version->id]);

            return back()->with('success', 'Versi Satsetui dipulihkan sebagai draf. Terapkan setelah memeriksanya.');
        }

        $site->update(['active_theme_version_id' => $version->id, 'draft_theme_version_id' => null]);

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

    private function boundedString(mixed $value, int $maxBytes): string
    {
        if (! is_string($value) || strlen($value) > $maxBytes) {
            throw new RuntimeException('Konten template Satsetui melebihi batas ukuran.');
        }

        return $value;
    }

    private function nullableBoundedString(mixed $value, int $maxLength): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (! is_string($value) || mb_strlen($value) > $maxLength) {
            throw new RuntimeException('Metadata halaman Satsetui tidak valid.');
        }

        return $value;
    }

    private function reservedPageSlug(string $slug): bool
    {
        return in_array($slug, ['produk', 'layanan', 'keranjang', 'checkout', 'privasi', 'lapor', 'robots-txt', 'sitemap-xml', 'halaman'], true);
    }
}
