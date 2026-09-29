<?php

namespace Tests\Feature\Storefront;

use App\Models\BusinessSite;
use App\Models\Lead;
use App\Models\Service;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class StorefrontLeadSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_lead_form_exposes_idempotency_key_and_accessible_validation_errors(): void
    {
        $html = view('storefront.slots.lead-form', ['services' => collect()])
            ->withErrors(['name' => 'Nama wajib diisi.'])
            ->render();

        preg_match('/name="idempotency_key" value="([^"]+)"/', $html, $matches);
        $this->assertTrue(isset($matches[1]) && Str::isUuid($matches[1]));
        $this->assertStringContainsString('aria-describedby="name-error"', $html);
        $this->assertStringContainsString('id="name-error" role="alert"', $html);
    }

    public function test_public_service_lead_is_saved_for_the_resolved_tenant_and_retries_are_idempotent(): void
    {
        $tenant = Tenant::factory()->create();
        $site = BusinessSite::factory()->create([
            'tenant_id' => $tenant->id,
            'slug' => 'jasa-jahit',
            'mode' => 'jasa',
            'status' => 'published',
        ]);
        $service = Service::create([
            'tenant_id' => $tenant->id,
            'code' => 'SVC-JAHIT',
            'name' => 'Jasa Jahit',
            'price' => 100000,
            'is_active' => true,
            'is_public' => true,
        ]);
        $payload = [
            'idempotency_key' => (string) Str::uuid(),
            'service_id' => $service->id,
            'name' => 'Dewi',
            'phone' => '081234567890',
            'message' => 'Minta jahit kebaya untuk acara keluarga.',
            'consent' => '1',
            '_hp_site_lead' => '',
        ];
        $url = 'http://jasa-jahit.fabriku.biz.id/prospek';

        $this->post($url, $payload)
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->post($url, $payload)
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseCount('leads', 1);
        $this->assertDatabaseHas('leads', [
            'tenant_id' => $tenant->id,
            'business_site_id' => $site->id,
            'service_id' => $service->id,
            'source' => 'website',
            'name' => 'Dewi',
            'consent_text' => 'Saya bersedia dihubungi oleh pihak toko terkait penawaran layanan ini sesuai dengan UU Perlindungan Data Pribadi (UU No. 27/2022).',
        ]);
        $this->assertNotNull(Lead::query()->first()->consent_at);
    }

    public function test_public_lead_cannot_reference_another_tenants_service(): void
    {
        $tenant = Tenant::factory()->create();
        BusinessSite::factory()->create([
            'tenant_id' => $tenant->id,
            'slug' => 'salon-satu',
            'mode' => 'jasa',
            'status' => 'published',
        ]);
        $otherTenant = Tenant::factory()->create();
        $otherService = Service::create([
            'tenant_id' => $otherTenant->id,
            'code' => 'SVC-RAHASIA',
            'name' => 'Layanan tenant lain',
            'price' => 100000,
            'is_active' => true,
            'is_public' => true,
        ]);

        $this->post('http://salon-satu.fabriku.biz.id/prospek', [
            'idempotency_key' => (string) Str::uuid(),
            'service_id' => $otherService->id,
            'name' => 'Rina',
            'phone' => '081234567890',
            'consent' => '1',
        ])->assertSessionHasErrors('service_id');

        $this->assertDatabaseCount('leads', 0);
    }

    public function test_authenticated_user_context_does_not_override_the_storefront_tenant(): void
    {
        $tenant = Tenant::factory()->create();
        BusinessSite::factory()->create([
            'tenant_id' => $tenant->id,
            'slug' => 'salon-target',
            'mode' => 'jasa',
            'status' => 'published',
        ]);
        $otherTenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $otherTenant->id]);

        $this->actingAs($user)->post('http://salon-target.fabriku.biz.id/prospek', [
            'idempotency_key' => (string) Str::uuid(),
            'name' => 'Rina',
            'phone' => '081234567890',
            'consent' => '1',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('leads', [
            'tenant_id' => $tenant->id,
            'name' => 'Rina',
        ]);
        $this->assertDatabaseMissing('leads', [
            'tenant_id' => $otherTenant->id,
            'name' => 'Rina',
        ]);
    }

    public function test_honeypot_submission_is_acknowledged_without_storing_a_lead(): void
    {
        Tenant::factory()->create();
        BusinessSite::factory()->create([
            'slug' => 'jasa-palsu',
            'mode' => 'jasa',
            'status' => 'published',
        ]);

        $this->post('http://jasa-palsu.fabriku.biz.id/prospek', [
            '_hp_site_lead' => 'bot-fill',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseCount('leads', 0);
    }

    public function test_public_lead_submissions_are_limited_per_ip(): void
    {
        Tenant::factory()->create();
        BusinessSite::factory()->create([
            'slug' => 'jasa-rate-limit',
            'mode' => 'jasa',
            'status' => 'published',
        ]);
        $url = 'http://jasa-rate-limit.fabriku.biz.id/prospek';

        foreach (range(1, 5) as $attempt) {
            $this->post($url, [
                'idempotency_key' => (string) Str::uuid(),
                'name' => 'Pengunjung '.$attempt,
                'phone' => '081234567890',
                'consent' => '1',
            ])->assertRedirect();
        }

        $this->post($url, [
            'idempotency_key' => (string) Str::uuid(),
            'name' => 'Pengunjung keenam',
            'phone' => '081234567890',
            'consent' => '1',
        ])->assertTooManyRequests();

        $this->assertDatabaseCount('leads', 5);
    }
}
