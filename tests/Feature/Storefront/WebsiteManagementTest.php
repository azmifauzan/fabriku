<?php

namespace Tests\Feature\Storefront;

use App\Models\BusinessSite;
use App\Models\InventoryItem;
use App\Models\Lead;
use App\Models\Service;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
