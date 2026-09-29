<?php

namespace Tests\Feature\Storefront;

use App\Models\AdminUser;
use App\Models\BusinessSite;
use App\Models\SiteReport;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_report_reaches_admin_and_admin_can_suspend_site(): void
    {
        $site = BusinessSite::factory()->create(['tenant_id' => Tenant::factory()->create()->id, 'slug' => 'report-shop', 'status' => 'published']);
        $this->get('http://report-shop.fabriku.biz.id/lapor')->assertOk()->assertSee('Laporkan situs');
        $this->get('http://report-shop.fabriku.biz.id/privasi')->assertOk()->assertSee('Privasi pengunjung');
        $this->post('http://report-shop.fabriku.biz.id/lapor', ['category' => 'penipuan', 'details' => 'Pesanan tidak dikirim sesuai keterangan.', 'contact_email' => 'reporter@example.test'])->assertRedirect();
        $report = SiteReport::firstOrFail();
        $this->assertSame($site->id, $report->business_site_id);

        $admin = AdminUser::factory()->create();
        $this->actingAs($admin, 'admin')->get('/admin/websites')->assertOk();
        $this->post('/admin/websites/'.$site->id.'/status', ['action' => 'suspend'])->assertRedirect();
        $this->assertSame('suspended', $site->fresh()->status);
        $this->get('http://report-shop.fabriku.biz.id/')->assertForbidden();
    }
}
