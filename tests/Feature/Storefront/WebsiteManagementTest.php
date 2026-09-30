<?php

namespace Tests\Feature\Storefront;

use App\Models\BusinessSite;
use App\Models\InventoryItem;
use App\Models\Lead;
use App\Models\Service;
use App\Models\SiteThemeVersion;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class WebsiteManagementTest extends TestCase
{
    use RefreshDatabase;

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
        config([
            'services.satsetui.base_url' => 'https://satsetui.test',
            'services.satsetui.integration_secret' => 'shared-test-secret',
        ]);
        Http::fake(function ($request) {
            if (str_ends_with($request->url(), '/api/integrations/fabriku/launch')) {
                return Http::response(['redirect_url' => 'https://satsetui.test/integrations/fabriku/continue/'.Str::random(60)]);
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
            ->assertRedirect();
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
        $item = InventoryItem::factory()->create(['tenant_id' => $tenant->id, 'product_code' => 'PK-1']);
        $otherItem = InventoryItem::factory()->create(['tenant_id' => $other->id, 'product_code' => 'PK-2']);
        $service = Service::create(['tenant_id' => $tenant->id, 'code' => 'S-1', 'name' => 'Kelas Kopi', 'price' => 50000]);
        $otherService = Service::create(['tenant_id' => $other->id, 'code' => 'S-2', 'name' => 'Jasa Lain', 'price' => 50000]);
        $this->actingAs($manager);

        $this->post('/website/products', ['product_code' => $item->product_code, 'slug' => 'kopi-bubuk', 'title' => 'Kopi Bubuk', 'is_visible' => 1])->assertRedirect();
        $this->assertDatabaseHas('site_products', ['tenant_id' => $tenant->id, 'slug' => 'kopi-bubuk']);
        $this->post('/website/products', ['product_code' => $otherItem->product_code, 'slug' => 'milik-lain', 'title' => 'Salah', 'is_visible' => 1])->assertSessionHasErrors('product_code');
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
}
