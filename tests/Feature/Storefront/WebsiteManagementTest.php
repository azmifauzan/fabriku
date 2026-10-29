<?php

namespace Tests\Feature\Storefront;

use App\Models\BusinessSite;
use App\Models\InventoryItem;
use App\Models\Lead;
use App\Models\Service;
use App\Models\SiteProduct;
use App\Models\SiteThemeVersion;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Storefront\Storefront;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;
use ZipArchive;

class WebsiteManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_uploaded_template_can_be_previewed_and_activated_without_changing_the_existing_design_on_upload(): void
    {
        $tenant = Tenant::factory()->create();
        $manager = User::factory()->forTenant($tenant->id)->manager()->create();
        $site = BusinessSite::factory()->create(['tenant_id' => $tenant->id, 'slug' => 'template-test', 'status' => 'published']);
        $this->actingAs($manager)->post('/website/theme/upload', [
            'name' => 'Toko saya',
            'template' => $this->themeZip(['index.html' => '<html><head><style>.hero{color:red}</style></head><body><h1 class="hero">Halo</h1><div data-fb-slot="products"></div></body></html>', 'tentang.html' => '<h1>Tentang kami</h1>']),
        ])->assertSessionHasNoErrors();
        $version = $site->themeVersions()->firstOrFail();
        $this->assertNull($site->fresh()->active_theme_version_id);
        $this->assertSame('Toko saya', $version->theme['name']);
        $this->get('/website/theme/'.$version->id.'/preview')->assertOk()->assertSee('Halo')->assertSee('.hero{color:red}', false);
        $this->post('/website/theme/'.$version->id.'/restore')->assertRedirect();
        $this->assertSame($version->id, $site->fresh()->active_theme_version_id);
        $this->get('http://template-test.fabriku.biz.id/')->assertOk()->assertSee('Halo');
        $this->get('http://template-test.fabriku.biz.id/halaman/tentang')->assertOk()->assertSee('Tentang kami');
        $this->get('http://template-test.fabriku.biz.id/sitemap.xml')->assertOk()->assertSee('/halaman/tentang');
    }

    public function test_uploaded_template_rejects_unsafe_and_unknown_content_and_requires_manager(): void
    {
        $tenant = Tenant::factory()->create();
        $manager = User::factory()->forTenant($tenant->id)->manager()->create();
        $site = BusinessSite::factory()->create(['tenant_id' => $tenant->id]);
        foreach (['<script>alert(1)</script>', '<div onclick="alert(1)">x</div>', '<style>@import "https://evil.test";</style><p>x</p>', '<style>p{background:image-set("https://evil.test/x" 1x)}</style><p>x</p>', '<div data-fb-slot="unknown"></div>'] as $html) {
            $this->actingAs($manager)->post('/website/theme/upload', ['name' => 'Invalid', 'template' => $this->themeZip(['index.html' => $html])])
                ->assertSessionHasErrors('template');
        }
        $this->actingAs($manager)->post('/website/theme/upload', ['name' => 'Invalid', 'template' => $this->themeZip(['index.html' => '<p>x</p>', '../escape.html' => '<p>x</p>'])])->assertSessionHasErrors('template');
        $staff = User::factory()->forTenant($tenant->id)->create(['role' => 'staff']);
        $this->actingAs($staff)->post('/website/theme/upload', ['name' => 'Invalid', 'template' => $this->themeZip(['index.html' => '<p>x</p>'])])->assertForbidden();
        $this->assertSame(0, $site->themeVersions()->count());
    }

    private function themeZip(array $files): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'theme');
        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::OVERWRITE);
        foreach ($files as $name => $content) {
            $zip->addFromString($name, $content);
        }
        $zip->close();

        return new UploadedFile($path, 'tema.zip', 'application/zip', null, true);
    }

    public function test_manager_can_preview_each_saved_design_without_changing_the_active_version(): void
    {
        $tenant = Tenant::factory()->create();
        $manager = User::factory()->forTenant($tenant->id)->manager()->create();
        $site = BusinessSite::factory()->create(['tenant_id' => $tenant->id, 'status' => 'draft']);
        $versions = collect([1, 2])->map(fn ($number) => SiteThemeVersion::create([
            'tenant_id' => $tenant->id,
            'business_site_id' => $site->id,
            'version' => $number,
            'source' => $number === 1 ? 'catalog' : 'satsetui',
            'theme' => ['satsetui_css' => '.hero{color:#123456}'],
            'sections' => [['type' => 'hero', 'html' => '<h1>Desain versi '.$number.'</h1><script>alert(1)</script>']],
            'created_by' => $manager->id,
        ]));
        $site->update(['active_theme_version_id' => $versions[0]->id, 'draft_theme_version_id' => $versions[1]->id]);
        $this->actingAs($manager);

        foreach ($versions as $version) {
            $this->get('/website/theme/'.$version->id.'/preview')
                ->assertOk()
                ->assertHeader('Content-Type', 'text/html; charset=UTF-8')
                ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
                ->assertHeader('Content-Security-Policy', "sandbox; form-action 'none'; base-uri 'none'; frame-ancestors 'self'")
                ->assertSee('Desain versi '.$version->version)
                ->assertSee('.hero{color:#123456}', false)
                ->assertDontSee('<script', false);
        }

        $this->assertSame($versions[0]->id, $site->fresh()->active_theme_version_id);
        $this->assertSame($versions[1]->id, $site->fresh()->draft_theme_version_id);
    }

    public function test_design_preview_is_private_to_the_site_manager(): void
    {
        $tenant = Tenant::factory()->create();
        $manager = User::factory()->forTenant($tenant->id)->manager()->create();
        $site = BusinessSite::factory()->create(['tenant_id' => $tenant->id]);
        $version = SiteThemeVersion::create([
            'tenant_id' => $tenant->id,
            'business_site_id' => $site->id,
            'version' => 1,
            'source' => 'catalog',
            'theme' => [],
            'sections' => [],
            'created_by' => $manager->id,
        ]);
        $url = '/website/theme/'.$version->id.'/preview';
        $this->get($url)->assertRedirect(route('login'));
        $otherManager = User::factory()->manager()->create();
        BusinessSite::factory()->create(['tenant_id' => $otherManager->tenant_id]);
        $this->actingAs($otherManager)->get($url)->assertNotFound();
        $staff = User::factory()->forTenant($tenant->id)->create(['role' => 'staff']);
        $this->actingAs($staff)->get($url)->assertForbidden();
    }

    public function test_manager_can_create_publish_and_edit_website(): void
    {
        $tenant = Tenant::factory()->create();
        $manager = User::factory()->forTenant($tenant->id)->manager()->create();
        $this->actingAs($manager);
        $this->post('/website', ['slug' => 'studio-kopi', 'mode' => 'gabungan', 'name' => 'Studio Kopi', 'description' => 'Kopi dan layanan kelas', 'whatsapp' => '08123456789'])->assertRedirect();
        $this->post('/website', ['slug' => 'Studio_Kopi', 'mode' => 'gabungan', 'name' => 'Studio Kopi'])->assertSessionHasErrors('slug');
        $this->post('/website', ['slug' => 'fallback', 'mode' => 'gabungan', 'name' => 'Studio Kopi'])->assertSessionHasErrors('slug');
        $this->post('/website', ['slug' => 'sites', 'mode' => 'gabungan', 'name' => 'Studio Kopi'])->assertSessionHasErrors('slug');
        $site = BusinessSite::where('tenant_id', $tenant->id)->firstOrFail();
        $this->assertSame('draft', $site->status);
        $this->get('/website')->assertOk();
        $this->post('/website/theme', ['primary' => '#123456', 'accent' => '#654321', 'background' => '#ffffff', 'text' => '#111111', 'hero_title' => 'Kopi pilihan', 'hero_subtitle' => 'Dari dapur kami', 'preset' => 'studio'])->assertRedirect();
        $this->post('/website/setup/complete')->assertRedirect();
        $this->assertNotNull($site->fresh()->setup_completed_at);
        $this->post('/website/publish', ['status' => 'published'])->assertRedirect();
        $this->get('http://studio-kopi.fabriku.biz.id/')->assertOk()->assertSee('Kopi pilihan');
        $this->assertSame(1, $site->fresh()->themeVersions()->count());
    }

    public function test_manager_can_launch_satsetui_and_import_a_sanitized_template(): void
    {
        $tenant = Tenant::factory()->create();
        $manager = User::factory()->forTenant($tenant->id)->manager()->create();
        $site = BusinessSite::factory()->create(['tenant_id' => $tenant->id, 'slug' => 'studio-kopi']);
        $activeVersion = SiteThemeVersion::create([
            'tenant_id' => $tenant->id,
            'business_site_id' => $site->id,
            'source' => 'catalog',
            'version' => 1,
            'theme' => ['preset' => 'ruang'],
            'sections' => [[
                'type' => 'hero',
                'sort' => 0,
                'is_visible' => true,
                'variables' => [],
                'html' => '<section><h1>Desain lama</h1></section>',
            ]],
            'created_by' => $manager->id,
        ]);
        $site->update(['active_theme_version_id' => $activeVersion->id]);
        $ticket = Str::random(60);
        $redirectUrl = 'https://satsetui.test/integrations/fabriku/continue/'.Str::random(60);
        config([
            'services.satsetui.base_url' => 'https://satsetui.test',
            'services.satsetui.integration_secret' => 'shared-test-secret',
        ]);
        Http::fake(function ($request) use ($redirectUrl) {
            if (str_ends_with($request->url(), '/api/integrations/fabriku/launch')) {
                return Http::response(['redirect_url' => $redirectUrl]);
            }

            if (str_contains($request->url(), '/api/integrations/fabriku/exports/')) {
                return Http::response([
                    'generation_id' => 314,
                    'template' => [
                        'format' => 'fabriku-site-v1',
                        'home' => [
                            'html' => '<main><h1>Studio Kopi</h1><script>alert(1)</script></main>',
                            'css' => '.hero{color:#123456}',
                            'seo_title' => 'Kopi Pilihan',
                            'seo_description' => 'Kopi dari Studio Kopi.',
                        ],
                        'pages' => [[
                            'slug' => 'tentang-kami',
                            'title' => 'Tentang Kami',
                            'html' => '<main><h1>Cerita kami</h1></main>',
                            'seo_title' => 'Cerita Studio Kopi',
                            'seo_description' => 'Perjalanan kedai kami.',
                        ]],
                    ],
                ]);
            }

            return Http::response([], 404);
        });

        $this->actingAs($manager)
            ->post('/website/satsetui/launch')
            ->assertRedirect($redirectUrl);
        $this->post('/website/satsetui/launch', [], ['X-Inertia' => 'true'])
            ->assertStatus(409)
            ->assertHeader('X-Inertia-Location', $redirectUrl)
            ->assertHeaderMissing('Location');
        $this->get('/website/satsetui/import?ticket='.$ticket)
            ->assertRedirect(route('website.index', ['tab' => 'desain']));

        $version = SiteThemeVersion::query()->where('business_site_id', $site->id)->where('satsetui_generation_id', '314')->firstOrFail();
        $this->assertSame('satsetui', $version->source);
        $this->assertSame('314', $version->satsetui_generation_id);
        $this->assertStringNotContainsString('<script', $version->sections[0]['html']);
        $this->assertStringContainsString('.hero{color:#123456}', $version->theme['satsetui_css']);
        $this->assertSame($activeVersion->id, $site->fresh()->active_theme_version_id);
        $this->assertSame($version->id, $site->fresh()->draft_theme_version_id);
        $this->assertSame(1, count($version->theme['satsetui_pages']));
        $this->assertDatabaseMissing('site_content_pages', [
            'business_site_id' => $site->id,
            'slug' => 'tentang-kami',
        ]);
        $this->assertSame($site->seo, $site->fresh()->seo);
        $this->get('http://studio-kopi.fabriku.biz.id/')->assertOk()->assertSee('Desain lama');
        $this->get('http://studio-kopi.fabriku.biz.id/halaman/tentang-kami')->assertNotFound();

        $this->post('/website/satsetui/publish-draft')->assertRedirect();
        $this->assertSame($version->id, $site->fresh()->active_theme_version_id);
        $this->assertNull($site->fresh()->draft_theme_version_id);
        $this->assertSame('published', $site->fresh()->status);
        $this->assertSame('Kopi Pilihan', $site->fresh()->seo['title']);
        $this->assertDatabaseHas('site_content_pages', [
            'business_site_id' => $site->id,
            'slug' => 'tentang-kami',
            'show_in_footer' => true,
            'is_published' => true,
        ]);
        $this->get('http://studio-kopi.fabriku.biz.id/')->assertOk()->assertSee('Studio Kopi')->assertDontSee('Desain lama');
        $this->get('http://studio-kopi.fabriku.biz.id/halaman/tentang-kami')->assertOk()->assertSee('Cerita kami');

        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/api/integrations/fabriku/launch')
            && $request->hasHeader('Authorization', 'Bearer shared-test-secret')
            && $request['fabriku_user_id'] === $manager->id
            && $request['site_id'] === $site->id);
    }

    public function test_products_services_and_recipients_are_tenant_scoped(): void
    {
        $tenant = Tenant::factory()->create();
        $other = Tenant::factory()->create();
        $manager = User::factory()->forTenant($tenant->id)->create();
        $otherUser = User::factory()->forTenant($other->id)->create();
        BusinessSite::factory()->create(['tenant_id' => $tenant->id, 'status' => 'draft']);
        // product_code stays null for items created through the inventory UI.
        $item = InventoryItem::factory()->create(['tenant_id' => $tenant->id, 'product_code' => null, 'status' => 'available', 'current_quantity' => 5, 'reserved_quantity' => 0]);
        $otherItem = InventoryItem::factory()->create(['tenant_id' => $other->id]);
        $service = Service::create(['tenant_id' => $tenant->id, 'code' => 'S-1', 'name' => 'Kelas Kopi', 'price' => 50000]);
        $otherService = Service::create(['tenant_id' => $other->id, 'code' => 'S-2', 'name' => 'Jasa Lain', 'price' => 50000]);
        $this->actingAs($manager);

        $this->get('/website?tab=katalog')->assertInertia(fn ($page) => $page->has('catalog', 1)->where('catalog.0.id', $item->id));
        $this->post('/website/products', ['inventory_item_id' => $item->id, 'slug' => 'kopi-bubuk', 'title' => 'Kopi Bubuk', 'is_visible' => 1])->assertRedirect();
        $this->assertDatabaseHas('site_products', ['tenant_id' => $tenant->id, 'slug' => 'kopi-bubuk']);
        $this->post('/website/products', ['inventory_item_id' => $otherItem->id, 'slug' => 'milik-lain', 'title' => 'Salah', 'is_visible' => 1])->assertSessionHasErrors('inventory_item_id');
        $this->post('/website/services/'.$service->id, ['is_public' => 1, 'slug' => 'kelas-kopi'])->assertRedirect();
        $this->post('/website/services/'.$otherService->id, ['is_public' => 1, 'slug' => 'milik-lain'])->assertNotFound();
        $this->post('/website/recipients', ['user_ids' => [$otherUser->id]])->assertSessionHasErrors('user_ids.0');
    }

    public function test_lead_can_be_contacted_and_converted_once_only_by_own_tenant(): void
    {
        $tenant = Tenant::factory()->create();
        $manager = User::factory()->forTenant($tenant->id)->create();
        $site = BusinessSite::factory()->create(['tenant_id' => $tenant->id]);
        $service = Service::create(['tenant_id' => $tenant->id, 'code' => 'S-1', 'name' => 'Kelas Kopi', 'price' => 50000]);
        $lead = Lead::create(['tenant_id' => $tenant->id, 'business_site_id' => $site->id, 'service_id' => $service->id, 'source' => 'website', 'name' => 'Calon pelanggan', 'phone' => '081234567890', 'consent_at' => now(), 'consent_text' => 'Setuju', 'idempotency_key' => Str::uuid()]);
        $other = User::factory()->forTenant(Tenant::factory()->create()->id)->create();
        $this->actingAs($other)->post('/website/leads/'.$lead->id.'/convert')->assertNotFound();
        $this->actingAs($manager)->post('/website/leads/'.$lead->id.'/status', ['status' => 'contacted'])->assertRedirect();
        $this->post('/website/leads/'.$lead->id.'/convert')->assertRedirect();
        $this->post('/website/leads/'.$lead->id.'/convert')->assertRedirect();
        $this->assertDatabaseCount('sales_orders', 1);
        $this->assertSame('converted', $lead->fresh()->status);
    }

    public function test_closing_a_lead_requires_status_and_reason(): void
    {
        $tenant = Tenant::factory()->create();
        $manager = User::factory()->forTenant($tenant->id)->create();
        $site = BusinessSite::factory()->create(['tenant_id' => $tenant->id]);
        $lead = Lead::create(['tenant_id' => $tenant->id, 'business_site_id' => $site->id, 'source' => 'website', 'name' => 'Calon pelanggan', 'phone' => '081234567890', 'consent_at' => now(), 'consent_text' => 'Setuju', 'idempotency_key' => Str::uuid()]);
        $this->actingAs($manager);

        $this->post('/website/leads/'.$lead->id.'/status', ['closed_reason' => 'Tidak jadi'])->assertSessionHasErrors('status');
        $this->post('/website/leads/'.$lead->id.'/status', ['status' => 'closed'])->assertSessionHasErrors('closed_reason');
        $this->assertSame('new', $lead->fresh()->status);

        $this->post('/website/leads/'.$lead->id.'/status', ['status' => 'closed', 'closed_reason' => 'Tidak jadi'])->assertRedirect();
        $this->assertSame('closed', $lead->fresh()->status);
        $this->assertSame('Tidak jadi', $lead->fresh()->closed_reason);
    }

    public function test_manager_can_save_fabriku_theme_when_satsetui_is_active_without_carrying_over_template(): void
    {
        $tenant = Tenant::factory()->create();
        $manager = User::factory()->forTenant($tenant->id)->manager()->create();
        $site = BusinessSite::factory()->create(['tenant_id' => $tenant->id, 'status' => 'published']);
        $satsetuiVersion = SiteThemeVersion::create([
            'tenant_id' => $tenant->id,
            'business_site_id' => $site->id,
            'source' => 'satsetui',
            'version' => 1,
            'theme' => ['preset' => 'ruang'],
            'sections' => [[
                'type' => 'satsetui-template',
                'sort' => 0,
                'is_visible' => true,
                'variables' => [],
                'html' => '<main><h1>Template Satsetui</h1></main>',
            ]],
            'created_by' => $manager->id,
        ]);
        $site->update(['active_theme_version_id' => $satsetuiVersion->id]);

        $this->actingAs($manager)
            ->post('/website/theme', [
                'primary' => '#123456',
                'accent' => '#654321',
                'background' => '#ffffff',
                'text' => '#111111',
                'hero_title' => 'Toko Baru',
                'hero_subtitle' => 'Deskripsi Toko',
                'preset' => 'ruang',
            ])
            ->assertRedirect();

        $newVersion = $site->fresh()->activeThemeVersion;
        $this->assertNotNull($newVersion);
        $this->assertSame('manual', $newVersion->source);
        $this->assertSame(2, $newVersion->version);

        // Sections should NOT contain the satsetui-template!
        $sectionTypes = collect($newVersion->sections)->pluck('type')->all();
        $this->assertNotContains('satsetui-template', $sectionTypes);
        $this->assertContains('hero', $sectionTypes);
    }

    public function test_manager_can_discard_satsetui_draft_and_keep_it_in_history(): void
    {
        $tenant = Tenant::factory()->create();
        $manager = User::factory()->forTenant($tenant->id)->manager()->create();
        $site = BusinessSite::factory()->create(['tenant_id' => $tenant->id]);
        $draft = SiteThemeVersion::create([
            'tenant_id' => $tenant->id,
            'business_site_id' => $site->id,
            'source' => 'satsetui',
            'version' => 1,
            'theme' => ['preset' => 'ruang'],
            'sections' => [],
            'created_by' => $manager->id,
        ]);
        $site->update(['draft_theme_version_id' => $draft->id]);

        $this->actingAs($manager)
            ->post('/website/satsetui/discard-draft')
            ->assertRedirect();

        $this->assertNull($site->fresh()->draft_theme_version_id);
        $this->assertDatabaseHas('site_theme_versions', ['id' => $draft->id]);
    }

    public function test_restoring_satsetui_version_becomes_draft_and_manual_version_becomes_active(): void
    {
        $tenant = Tenant::factory()->create();
        $manager = User::factory()->forTenant($tenant->id)->manager()->create();
        $site = BusinessSite::factory()->create(['tenant_id' => $tenant->id]);
        $manualVersion = SiteThemeVersion::create([
            'tenant_id' => $tenant->id, 'business_site_id' => $site->id, 'source' => 'manual', 'version' => 1,
            'theme' => ['preset' => 'ruang'], 'sections' => [], 'created_by' => $manager->id,
        ]);
        $satsetuiVersion = SiteThemeVersion::create([
            'tenant_id' => $tenant->id, 'business_site_id' => $site->id, 'source' => 'satsetui', 'version' => 2,
            'theme' => ['preset' => 'ruang', 'satsetui_pages' => [[
                'slug' => 'kebijakan-layanan', 'title' => 'Kebijakan Layanan', 'html' => '<p>Syarat kami</p>',
                'seo_title' => 'Kebijakan', 'seo_description' => 'Ketentuan layanan.', 'sort' => 1,
            ]]],
            'sections' => [], 'created_by' => $manager->id,
        ]);
        $site->update(['active_theme_version_id' => $manualVersion->id]);

        $this->actingAs($manager)->post('/website/theme/'.$satsetuiVersion->id.'/restore')->assertRedirect();

        // Satsetui version only becomes a draft: the live design and extra pages are untouched until applied.
        $this->assertSame($manualVersion->id, $site->fresh()->active_theme_version_id);
        $this->assertSame($satsetuiVersion->id, $site->fresh()->draft_theme_version_id);
        $this->assertDatabaseMissing('site_content_pages', ['business_site_id' => $site->id, 'slug' => 'kebijakan-layanan']);

        $this->post('/website/satsetui/publish-draft')->assertRedirect();
        $this->assertSame($satsetuiVersion->id, $site->fresh()->active_theme_version_id);
        $this->assertDatabaseHas('site_content_pages', ['business_site_id' => $site->id, 'slug' => 'kebijakan-layanan', 'is_published' => true]);

        // Going back to the Fabriku version keeps the Satsetui extra pages published.
        $this->post('/website/theme/'.$manualVersion->id.'/restore')->assertRedirect();
        $this->assertSame($manualVersion->id, $site->fresh()->active_theme_version_id);
        $this->assertDatabaseHas('site_content_pages', ['business_site_id' => $site->id, 'slug' => 'kebijakan-layanan', 'is_published' => true]);
    }

    public function test_changing_site_mode_resets_active_design_but_keeps_versions(): void
    {
        $tenant = Tenant::factory()->create();
        $manager = User::factory()->forTenant($tenant->id)->manager()->create();
        $site = BusinessSite::factory()->create(['tenant_id' => $tenant->id, 'mode' => 'produk', 'slug' => 'toko-mode']);
        $version = SiteThemeVersion::create([
            'tenant_id' => $tenant->id, 'business_site_id' => $site->id, 'source' => 'manual', 'version' => 1,
            'theme' => ['preset' => 'ruang'], 'sections' => [], 'created_by' => $manager->id,
        ]);
        $site->update(['active_theme_version_id' => $version->id]);

        $this->actingAs($manager)->post('/website', ['slug' => 'toko-mode', 'mode' => 'produk', 'name' => 'Toko'])->assertRedirect();
        $this->assertSame($version->id, $site->fresh()->active_theme_version_id);

        $this->post('/website', ['slug' => 'toko-mode', 'mode' => 'jasa', 'name' => 'Toko'])->assertRedirect();
        $this->assertNull($site->fresh()->active_theme_version_id);
        $this->assertDatabaseHas('site_theme_versions', ['id' => $version->id]);
    }

    public function test_product_move_swaps_neighbours_and_is_tenant_scoped(): void
    {
        $tenant = Tenant::factory()->create();
        $manager = User::factory()->forTenant($tenant->id)->manager()->create();
        BusinessSite::factory()->create(['tenant_id' => $tenant->id]);
        $make = fn (int $sort) => SiteProduct::factory()->create(['tenant_id' => $tenant->id, 'sort' => $sort]);
        [$a, $b, $c] = [$make(1), $make(2), $make(3)];

        $this->actingAs($manager)->post('/website/products/'.$b->id.'/move', ['direction' => 'down'])->assertRedirect();
        $this->assertSame([$a->id, $c->id, $b->id], SiteProduct::where('tenant_id', $tenant->id)->orderBy('sort')->orderBy('id')->pluck('id')->all());

        $this->post('/website/products/'.$a->id.'/move', ['direction' => 'up'])->assertRedirect();
        $this->assertSame([$a->id, $c->id, $b->id], SiteProduct::where('tenant_id', $tenant->id)->orderBy('sort')->orderBy('id')->pluck('id')->all());

        $this->post('/website/products/'.$b->id.'/move', ['direction' => 'sideways'])->assertSessionHasErrors('direction');

        $other = User::factory()->forTenant(Tenant::factory()->create()->id)->manager()->create();
        $this->actingAs($other)->post('/website/products/'.$a->id.'/move', ['direction' => 'down'])->assertNotFound();
        $this->delete('/website/products/'.$a->id)->assertNotFound();
        $this->post('/website/products', ['id' => $a->id, 'slug' => 'x', 'title' => 'X', 'is_visible' => 1])->assertNotFound();
        $this->post('/website/products/'.$a->id.'/toggle')->assertNotFound();
    }

    public function test_service_move_swaps_neighbours_from_unnormalized_order(): void
    {
        $tenant = Tenant::factory()->create();
        $manager = User::factory()->forTenant($tenant->id)->manager()->create();
        BusinessSite::factory()->create(['tenant_id' => $tenant->id]);
        $make = fn (string $code, int $sort) => Service::create(['tenant_id' => $tenant->id, 'code' => $code, 'name' => $code, 'price' => 1000, 'sort' => $sort]);
        [$a, $b, $c] = [$make('S-A', 1), $make('S-B', 2), $make('S-C', 3)];

        $this->actingAs($manager)->post('/website/services/'.$b->id.'/move', ['direction' => 'down'])->assertRedirect();
        $this->assertSame([$a->id, $c->id, $b->id], Service::where('tenant_id', $tenant->id)->orderBy('sort')->orderBy('id')->pluck('id')->all());
    }

    public function test_manager_can_manage_product_lifecycle_on_website(): void
    {
        $tenant = Tenant::factory()->create();
        $manager = User::factory()->forTenant($tenant->id)->manager()->create();
        $site = BusinessSite::factory()->create(['tenant_id' => $tenant->id]);
        $item1 = InventoryItem::factory()->create(['tenant_id' => $tenant->id, 'current_quantity' => 10, 'reserved_quantity' => 0, 'status' => 'available']);
        $item2 = InventoryItem::factory()->create(['tenant_id' => $tenant->id, 'current_quantity' => 15, 'reserved_quantity' => 0, 'status' => 'available']);

        $this->actingAs($manager);

        // Add item1
        $this->post('/website/products', [
            'inventory_item_id' => $item1->id,
            'slug' => 'item-satu',
            'title' => 'Item Satu',
            'is_visible' => 1,
        ])->assertRedirect();
        $prod1 = SiteProduct::where('inventory_item_id', $item1->id)->firstOrFail();

        // Add item2
        $this->post('/website/products', [
            'inventory_item_id' => $item2->id,
            'slug' => 'item-dua',
            'title' => 'Item Dua',
            'is_visible' => 1,
        ])->assertRedirect();
        $prod2 = SiteProduct::where('inventory_item_id', $item2->id)->firstOrFail();

        // Edit item1 (linked inventory item cannot be changed)
        $this->post('/website/products', [
            'id' => $prod1->id,
            'slug' => 'item-satu-updated',
            'title' => 'Item Satu Updated',
            'is_visible' => 1,
        ])->assertRedirect();
        $this->assertSame('item-satu-updated', $prod1->fresh()->slug);
        $this->assertSame($item1->id, $prod1->fresh()->inventory_item_id);

        // Toggle visibility
        $this->post('/website/products/'.$prod1->id.'/toggle')->assertRedirect();
        $this->assertFalse($prod1->fresh()->is_visible);

        // Move products
        $this->post('/website/products/'.$prod2->id.'/move', ['direction' => 'up'])->assertRedirect();
        $this->assertTrue($prod2->fresh()->sort < $prod1->fresh()->sort);

        // Delete product from website
        $this->delete('/website/products/'.$prod1->id)->assertRedirect();
        $this->assertDatabaseMissing('site_products', ['id' => $prod1->id]);
        $this->assertDatabaseHas('inventory_items', ['id' => $item1->id]); // stock unharmed
    }

    public function test_manager_can_reorder_services(): void
    {
        $tenant = Tenant::factory()->create();
        $manager = User::factory()->forTenant($tenant->id)->manager()->create();
        $site = BusinessSite::factory()->create(['tenant_id' => $tenant->id]);
        $s1 = Service::create(['tenant_id' => $tenant->id, 'code' => 'S-1', 'name' => 'Layanan A', 'price' => 10000, 'sort' => 0, 'is_public' => true, 'is_active' => true]);
        $s2 = Service::create(['tenant_id' => $tenant->id, 'code' => 'S-2', 'name' => 'Layanan B', 'price' => 20000, 'sort' => 1, 'is_public' => true, 'is_active' => true]);

        $this->actingAs($manager);
        $this->post('/website/services/'.$s2->id.'/move', ['direction' => 'up'])->assertRedirect();

        $this->assertTrue($s2->fresh()->sort < $s1->fresh()->sort);

        // Verify Storefront query reflects the order
        $orderedServices = Storefront::for($tenant)->services()->get();
        $this->assertSame($s2->id, $orderedServices->first()->id);
    }
}
