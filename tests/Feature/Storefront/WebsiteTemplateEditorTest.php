<?php

namespace Tests\Feature\Storefront;

use App\Models\BusinessSite;
use App\Models\Service;
use App\Models\SiteContentPage;
use App\Models\SiteThemeVersion;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Storefront\ThemeRenderer;
use App\Services\Storefront\UploadedTheme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class WebsiteTemplateEditorTest extends TestCase
{
    use RefreshDatabase;

    private function managerSite(): BusinessSite
    {
        $tenant = Tenant::factory()->create();
        $manager = User::factory()->forTenant($tenant->id)->manager()->create();
        $this->actingAs($manager);

        return BusinessSite::factory()->create(['tenant_id' => $tenant->id, 'slug' => 'editor-test', 'mode' => 'gabungan', 'status' => 'published']);
    }

    private function createTemplate(array $files): SiteThemeVersion
    {
        $this->post('/website/templates', ['name' => 'Template Saya', 'files' => $files])->assertSessionHasNoErrors();

        return SiteThemeVersion::latest('id')->firstOrFail();
    }

    public function test_editor_preview_does_not_save_and_editing_updates_the_same_active_template(): void
    {
        $site = $this->managerSite();
        $files = ['index.html' => '<h1>Sebelum</h1>', 'theme.css' => 'h1{color:#123456}'];
        $template = $this->createTemplate($files);
        $this->assertNull($site->fresh()->active_theme_version_id);
        $this->get('/website/templates/'.$template->id.'/edit')->assertInertia(fn (Assert $page) => $page
            ->component('Website/TemplateEditor')->where('templateId', $template->id)->where('files', $files));
        $files['index.html'] = '<h1>Sesudah</h1>';
        $this->postJson('/website/templates/preview', ['name' => 'Template Saya', 'files' => $files])->assertOk()->assertSee('Sesudah')->assertHeader('Cache-Control', 'no-store, private');
        $this->assertStringContainsString('Sebelum', $template->fresh()->sections[0]['html']);
        $this->post('/website/templates/'.$template->id.'/activate')->assertSessionHasNoErrors();
        $this->patch('/website/templates/'.$template->id, ['name' => 'Template Baru', 'files' => $files, 'revision' => $template->fresh()->editorRevision()])->assertSessionHasNoErrors();
        $this->assertSame(1, $site->themeVersions()->count());
        $this->assertSame($template->id, $site->fresh()->active_theme_version_id);
        $this->get('http://editor-test.fabriku.biz.id/')->assertOk()->assertSee('Sesudah')->assertDontSee('Sebelum');
    }

    public function test_stale_or_unsafe_editor_changes_are_rejected_without_overwriting_data(): void
    {
        $this->managerSite();
        $template = $this->createTemplate(['index.html' => '<h1>Awal</h1>']);
        $revision = $template->editorRevision();
        $this->patch('/website/templates/'.$template->id, ['name' => 'Changed', 'files' => ['index.html' => '<h1>Terbaru</h1>'], 'revision' => $revision])->assertSessionHasNoErrors();
        $this->patch('/website/templates/'.$template->id, ['name' => 'Stale', 'files' => ['index.html' => '<h1>Tertimpa</h1>'], 'revision' => $revision])->assertSessionHasErrors('revision');
        foreach ([
            ['index.html' => '<script>alert(1)</script>'],
            ['index.html' => '<h1>Aman</h1>', '../escape.html' => '<p>x</p>'],
            ['index.html' => '<p>x</p>', 'theme.css' => 'p{background:image-set("https://evil.test" 1x)}'],
            ['index.html' => '<p>x</p>', 'theme.css' => 'a[href="https://safe.test"]{color:red} p{background:image-set("https://evil.test" 1x)}'],
            ['index.html' => '<div data-fb-slot="products"><div data-fb-slot="services"></div></div>'],
        ] as $files) {
            $this->patch('/website/templates/'.$template->id, ['name' => 'Unsafe', 'files' => $files, 'revision' => $template->fresh()->editorRevision()])->assertSessionHasErrors('template');
        }
        $this->assertSame('Changed', $template->fresh()->theme['name']);
        $this->assertStringContainsString('Terbaru', $template->fresh()->sections[0]['html']);
    }

    public function test_editor_accepts_empty_css_but_rejects_empty_html(): void
    {
        $this->managerSite();
        $template = $this->createTemplate(['index.html' => '<h1>Valid</h1>', 'empty.css' => '', 'comments.css' => '/* Custom CSS */']);
        $this->assertSame('', $template->theme['editor_files']['empty.css']);
        $this->patch('/website/templates/'.$template->id, ['name' => 'Empty', 'files' => ['index.html' => ''], 'revision' => $template->editorRevision()])->assertSessionHasErrors('template');
        $this->assertStringContainsString('Valid', $template->fresh()->sections[0]['html']);
    }

    public function test_template_editor_and_activation_are_scoped_to_manager_and_tenant(): void
    {
        $site = $this->managerSite();
        $template = $this->createTemplate(['index.html' => '<h1>Rahasia</h1>']);
        $other = User::factory()->manager()->create();
        BusinessSite::factory()->create(['tenant_id' => $other->tenant_id]);
        $this->actingAs($other)->get('/website/templates/'.$template->id.'/edit')->assertNotFound();
        $this->patch('/website/templates/'.$template->id, [])->assertNotFound();
        $this->post('/website/templates/'.$template->id.'/activate')->assertNotFound();
        $this->delete('/website/templates/'.$template->id)->assertNotFound();
        $staff = User::factory()->forTenant($site->tenant_id)->create(['role' => 'staff']);
        $this->actingAs($staff)->get('/website/templates/'.$template->id.'/edit')->assertForbidden();
        $this->post('/website/templates/preview', [])->assertForbidden();
        $this->patch('/website/templates/'.$template->id, [])->assertForbidden();
        $this->post('/website/templates/'.$template->id.'/activate')->assertForbidden();
        $this->delete('/website/templates/'.$template->id)->assertForbidden();
    }

    public function test_only_inactive_custom_templates_can_be_deleted(): void
    {
        $site = $this->managerSite();
        $active = $this->createTemplate(['index.html' => '<h1>Aktif</h1>']);
        $draft = $this->createTemplate(['index.html' => '<h1>Draft</h1>']);
        $site->update(['active_theme_version_id' => $active->id, 'draft_theme_version_id' => $draft->id]);

        $this->delete('/website/templates/'.$active->id)->assertSessionHasErrors('templateDelete');
        $this->assertDatabaseHas('site_theme_versions', ['id' => $active->id]);
        $this->delete('/website/templates/'.$draft->id)->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('site_theme_versions', ['id' => $draft->id]);
        $this->assertNull($site->fresh()->draft_theme_version_id);

        $this->post('/website/templates/presets/ruang/activate')->assertSessionHasNoErrors();
        $builtin = $site->fresh()->activeThemeVersion;
        $this->post('/website/templates/'.$active->id.'/activate')->assertSessionHasNoErrors();
        $this->delete('/website/templates/'.$builtin->id)->assertSessionHasErrors('templateDelete');
        $this->assertDatabaseHas('site_theme_versions', ['id' => $builtin->id]);
    }

    public function test_builtin_templates_remain_available_and_repeated_activation_does_not_create_versions(): void
    {
        $site = $this->managerSite();
        foreach (['ruang', 'etalase', 'studio'] as $preset) {
            $this->get('/website/templates/presets/'.$preset.'/edit')->assertOk()->assertInertia(fn (Assert $page) => $page->component('Website/TemplateEditor')->where('templateId', null));
            $this->post('/website/templates/presets/'.$preset.'/activate')->assertSessionHasNoErrors();
            $this->post('/website/templates/presets/'.$preset.'/activate')->assertSessionHasNoErrors();
        }
        $this->assertSame(3, $site->themeVersions()->count());
        $this->get('/website?tab=desain')->assertInertia(fn (Assert $page) => $page->has('templates', 0));
        $template = new SiteThemeVersion(['theme' => [], 'sections' => app(ThemeRenderer::class)->sectionsForPreset($site, 'studio')]);
        $files = app(UploadedTheme::class)->editorFiles($site, $template, app(ThemeRenderer::class));
        $custom = $this->createTemplate($files);
        $this->assertNotNull($custom->id);
        $this->assertSame(4, $site->themeVersions()->count());
    }

    public function test_extra_pages_keep_catalog_slots_shared_styles_and_footer_links(): void
    {
        $site = $this->managerSite();
        Service::create(['tenant_id' => $site->tenant_id, 'code' => 'SERV', 'slug' => 'service', 'name' => 'Layanan Tampil', 'price' => 1000, 'is_public' => true, 'is_active' => true]);
        $template = $this->createTemplate([
            'index.html' => '<style>.brand-header{color:#123456}</style><header data-fb-shell="header" class="brand-header">Brand</header><h1>Home</h1><div data-fb-slot="footer-links"></div>',
            'tentang.html' => '<h1>Tentang</h1><div data-fb-slot="services"></div>',
        ]);
        $this->post('/website/templates/'.$template->id.'/activate')->assertSessionHasNoErrors();
        $this->get('http://editor-test.fabriku.biz.id/halaman/tentang')->assertOk()->assertSee('Layanan Tampil')->assertSee('.brand-header{color:#123456}', false)->assertSee('brand-header');
        $this->get('http://editor-test.fabriku.biz.id/')->assertSee('href="/halaman/tentang"', false);
        $this->postJson('/website/templates/preview', ['name' => 'Preview', 'files' => $template->theme['editor_files'], 'page' => 'tentang.html'])->assertOk()->assertSee('Layanan Tampil');
    }

    public function test_satsetui_template_can_be_edited_and_activated_without_serving_stale_page_content(): void
    {
        $site = $this->managerSite();
        SiteContentPage::create(['tenant_id' => $site->tenant_id, 'business_site_id' => $site->id, 'slug' => 'tentang', 'title' => 'Tentang', 'html' => '<h1>Konten lama</h1>', 'is_published' => true]);
        $template = SiteThemeVersion::create([
            'tenant_id' => $site->tenant_id, 'business_site_id' => $site->id, 'source' => 'satsetui', 'version' => 1,
            'theme' => ['satsetui_css' => '.hero{color:#123456}', 'satsetui_pages' => [['slug' => 'tentang', 'title' => 'Tentang', 'html' => '<h1>Dari Satsetui</h1>']]],
            'sections' => [['html' => '<h1>Home</h1>', 'variables' => []]],
        ]);
        $files = app(UploadedTheme::class)->editorFiles($site, $template, app(ThemeRenderer::class));
        $files['tentang.html'] = '<h1>Diedit di Fabriku</h1>';
        $this->patch('/website/templates/'.$template->id, ['name' => 'Satsetui Kustom', 'files' => $files, 'revision' => $template->editorRevision()])->assertSessionHasNoErrors();
        $this->post('/website/templates/'.$template->id.'/activate')->assertSessionHasNoErrors();
        $this->get('http://editor-test.fabriku.biz.id/halaman/tentang')->assertOk()->assertSee('Diedit di Fabriku')->assertDontSee('Konten lama');
        $this->assertSame(1, $site->themeVersions()->count());
        $this->assertDatabaseHas('site_content_pages', ['id' => $site->contentPages()->first()->id, 'html' => '<h1>Konten lama</h1>']);
    }
}
