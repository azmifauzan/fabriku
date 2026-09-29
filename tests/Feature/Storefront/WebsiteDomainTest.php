<?php

namespace Tests\Feature\Storefront;

use App\Models\BusinessSite;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Storefront\CloudflareDomains;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WebsiteDomainTest extends TestCase
{
    use RefreshDatabase;

    public function test_domain_stays_pending_until_certificate_hostname_and_dns_are_ready(): void
    {
        $tenant = Tenant::factory()->create();
        $site = BusinessSite::factory()->create(['tenant_id' => $tenant->id]);
        $this->actingAs(User::factory()->forTenant($tenant->id)->create());
        config(['services.cloudflare_storefront.token' => 'test-token', 'services.cloudflare_storefront.zone_id' => 'zone-test']);
        Http::fake([
            'api.cloudflare.com/client/v4/zones/zone-test/custom_hostnames' => Http::response(['success' => true, 'result' => ['id' => 'hostname-id']], 200),
            'api.cloudflare.com/client/v4/zones/zone-test/custom_hostnames/hostname-id' => Http::response(['success' => true, 'result' => ['status' => 'active', 'ssl' => ['status' => 'active']]], 200),
        ]);
        $this->post('/website/domain', ['domain' => 'www.example.com'])->assertRedirect();
        $this->assertSame('pending', $site->fresh()->domain_status);
        $this->post('/website/domain/sync')->assertRedirect();
        $this->assertSame('pending', $site->fresh()->domain_status);

        $this->partialMock(CloudflareDomains::class)->shouldReceive('dnsPointsToTarget')->andReturn(true);
        $this->post('/website/domain/sync')->assertRedirect();
        $this->assertSame('active', $site->fresh()->domain_status);
        $this->get('http://'.$site->slug.'.fabriku.biz.id/')->assertRedirect('https://www.example.com/');
    }

    public function test_domain_registration_rejects_other_tenants_domain_and_reserved_host(): void
    {
        $tenant = Tenant::factory()->create();
        BusinessSite::factory()->create(['tenant_id' => $tenant->id]);
        BusinessSite::factory()->create(['tenant_id' => Tenant::factory()->create()->id, 'custom_domain' => 'www.owned.com']);
        $this->actingAs(User::factory()->forTenant($tenant->id)->create());
        $this->post('/website/domain', ['domain' => 'www.owned.com'])->assertSessionHasErrors('domain');
        $this->post('/website/domain', ['domain' => 'app.fabriku.biz.id'])->assertStatus(422);
        $this->post('/website/domain', ['domain' => 'app.fabriku.id'])->assertStatus(422);
    }
}
