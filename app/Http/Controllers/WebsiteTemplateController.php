<?php

namespace App\Http\Controllers;

use App\Models\BusinessSite;
use App\Models\SiteThemeVersion;
use App\Services\Storefront\ThemeRenderer;
use App\Services\Storefront\UploadedTheme;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class WebsiteTemplateController extends Controller
{
    private const PRESETS = ['ruang' => 'Ruang', 'etalase' => 'Etalase', 'studio' => 'Studio'];

    public function edit(Request $request, SiteThemeVersion $template, UploadedTheme $package, ThemeRenderer $renderer): Response
    {
        $site = $this->site($request, $template);
        if (isset($template->theme['builtin_preset'])) {
            return $this->editPreset($request, $template->theme['builtin_preset'], $package, $renderer);
        }

        return Inertia::render('Website/TemplateEditor', [
            'templateId' => $template->id,
            'name' => $template->theme['name'] ?? ($template->source === 'satsetui' ? 'Desain Satsetui' : 'Desain Fabriku'),
            'files' => $package->editorFiles($site, $template, $renderer),
            'revision' => $template->editorRevision(),
            'isActive' => $site->active_theme_version_id === $template->id,
        ]);
    }

    public function editPreset(Request $request, string $preset, UploadedTheme $package, ThemeRenderer $renderer): Response
    {
        $site = $this->site($request);
        abort_unless(isset(self::PRESETS[$preset]), 404);
        $template = new SiteThemeVersion(['theme' => [], 'sections' => $renderer->sectionsForPreset($site, $preset)]);

        return Inertia::render('Website/TemplateEditor', [
            'templateId' => null,
            'name' => self::PRESETS[$preset].' Kustom',
            'files' => $package->editorFiles($site, $template, $renderer),
            'revision' => null,
            'isActive' => false,
        ]);
    }

    public function store(Request $request, UploadedTheme $package): RedirectResponse
    {
        $site = $this->site($request);
        $data = $this->validateFiles($request);
        $parsed = $package->parseFiles($data['files']);
        $template = DB::transaction(function () use ($site, $data, $parsed, $package, $request) {
            $site = BusinessSite::whereKey($site->id)->lockForUpdate()->firstOrFail();

            return SiteThemeVersion::create(array_merge($package->attributes($parsed, $data['name']), [
                'tenant_id' => $site->tenant_id,
                'business_site_id' => $site->id,
                'source' => 'upload',
                'version' => ($site->themeVersions()->max('version') ?? 0) + 1,
                'created_by' => $request->user()->id,
            ]));
        });

        return redirect()->route('website.templates.edit', $template)->with('success', 'Template disimpan. Pilih Aktifkan di daftar template untuk menggunakannya.');
    }

    public function update(Request $request, SiteThemeVersion $template, UploadedTheme $package): RedirectResponse
    {
        $site = $this->site($request, $template);
        $data = $this->validateFiles($request);
        $request->validate(['revision' => ['required', 'string', 'size:64']]);
        $parsed = $package->parseFiles($data['files']);
        DB::transaction(function () use ($site, $template, $request, $package, $parsed, $data) {
            BusinessSite::whereKey($site->id)->lockForUpdate()->firstOrFail();
            $template = SiteThemeVersion::whereKey($template->id)->lockForUpdate()->firstOrFail();
            if (! hash_equals($template->editorRevision(), $request->string('revision')->toString())) {
                throw ValidationException::withMessages(['revision' => 'Template telah diubah di tab atau oleh pengguna lain. Muat ulang sebelum menyimpan agar perubahan tidak tertimpa.']);
            }
            $attributes = $package->attributes($parsed, $data['name'], $template->theme ?? []);
            if (in_array($template->source, ['manual', 'catalog'], true)) {
                $attributes['source'] = 'upload';
            }
            $template->update($attributes);
        });

        return back()->with('success', 'Perubahan template disimpan.');
    }

    public function activate(Request $request, SiteThemeVersion $template): RedirectResponse
    {
        $site = $this->site($request, $template);
        DB::transaction(function () use ($site, $template) {
            $site = BusinessSite::whereKey($site->id)->lockForUpdate()->firstOrFail();
            $template = SiteThemeVersion::whereKey($template->id)->lockForUpdate()->firstOrFail();
            $theme = $template->theme ?? [];
            $template->update(['theme' => array_merge($theme, ['uses_template_pages' => true])]);
            $site->update([
                'active_theme_version_id' => $template->id,
                'draft_theme_version_id' => null,
                'seo' => array_merge($site->seo ?? [], $theme['satsetui_home_seo'] ?? []),
            ]);
        });

        return back()->with('success', 'Template diaktifkan.');
    }

    public function destroy(Request $request, SiteThemeVersion $template): RedirectResponse
    {
        $site = $this->site($request, $template);
        DB::transaction(function () use ($site, $template) {
            $site = BusinessSite::whereKey($site->id)->lockForUpdate()->firstOrFail();
            $template = SiteThemeVersion::whereKey($template->id)->lockForUpdate()->firstOrFail();
            if ($site->active_theme_version_id === $template->id || isset($template->theme['builtin_preset'])) {
                throw ValidationException::withMessages(['templateDelete' => 'Template aktif atau bawaan Fabriku tidak dapat dihapus.']);
            }
            if ($site->draft_theme_version_id === $template->id) {
                $site->update(['draft_theme_version_id' => null]);
            }
            $template->delete();
        });

        return back()->with('success', 'Template dihapus.');
    }

    public function activatePreset(Request $request, string $preset, ThemeRenderer $renderer): RedirectResponse
    {
        $site = $this->site($request);
        abort_unless(isset(self::PRESETS[$preset]), 404);
        DB::transaction(function () use ($site, $preset, $renderer, $request) {
            $site = BusinessSite::whereKey($site->id)->lockForUpdate()->firstOrFail();
            $template = $site->themeVersions()->where('source', 'manual')->where('theme->builtin_preset', $preset)->first();
            if (! $template) {
                $template = SiteThemeVersion::create([
                    'tenant_id' => $site->tenant_id,
                    'business_site_id' => $site->id,
                    'source' => 'manual',
                    'version' => ($site->themeVersions()->max('version') ?? 0) + 1,
                    'theme' => ['name' => self::PRESETS[$preset], 'preset' => $preset, 'builtin_preset' => $preset],
                    'sections' => $renderer->sectionsForPreset($site, $preset),
                    'created_by' => $request->user()->id,
                ]);
            }
            $site->update(['active_theme_version_id' => $template->id, 'draft_theme_version_id' => null]);
        });

        return back()->with('success', 'Template '.self::PRESETS[$preset].' diaktifkan.');
    }

    public function preview(Request $request, UploadedTheme $package, ThemeRenderer $renderer): HttpResponse
    {
        $site = $this->site($request);
        $data = $this->validateFiles($request);
        $parsed = $package->parseFiles($data['files']);
        $template = new SiteThemeVersion($package->attributes($parsed, $data['name']));
        $filename = $request->input('page', 'index.html');
        abort_unless(is_string($filename) && isset($data['files'][$filename]) && str_ends_with($filename, '.html'), 404);
        $context = [];
        if ($filename !== 'index.html') {
            $slug = substr($filename, 0, -5);
            $page = $parsed['pages'][$slug];
            $context = ['page' => 'content_page', 'page_content' => $page['html'], 'page_css' => $page['css'], 'content_page' => (object) ['slug' => $slug]];
        }
        $html = $renderer->render($site, $template, $context);
        $html = str_replace('<head>', '<head><meta http-equiv="Content-Security-Policy" content="default-src &apos;none&apos;; img-src https: data:; style-src &apos;unsafe-inline&apos; https: http:; form-action &apos;none&apos;; base-uri &apos;none&apos;">', $html);

        return response($html)->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Cache-Control', 'private, no-store')->header('X-Robots-Tag', 'noindex, nofollow')
            ->header('Content-Security-Policy', "sandbox; form-action 'none'; base-uri 'none'; frame-ancestors 'self'");
    }

    private function validateFiles(Request $request): array
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100'], 'files' => ['required', 'array', 'max:30'], 'files.*' => ['present', 'nullable', 'string', 'max:500000']]);
        $data['files'] = array_map(fn ($content) => $content ?? '', $data['files']);

        return $data;
    }

    private function site(Request $request, ?SiteThemeVersion $template = null): BusinessSite
    {
        abort_unless($request->user()->isManager(), 403);
        $site = BusinessSite::where('tenant_id', $request->user()->tenant_id)->firstOrFail();
        abort_if($template && ($template->business_site_id !== $site->id || $template->tenant_id !== $site->tenant_id), 404);

        return $site;
    }
}
