<?php

namespace Tests\Feature\Storefront;

use App\Models\SubscriptionPayment;
use App\Models\Tenant;
use App\Models\UsageEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingFondasiTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_has_plan_code_defaulting_to_trial(): void
    {
        $tenant = Tenant::factory()->create();

        $this->assertEquals('trial', $tenant->plan_code);
        $this->assertNull($tenant->pro_expires_at);
        $this->assertFalse($tenant->pro_auto_renew);
    }

    public function test_tenant_has_pro_respects_expiry_and_grace_period(): void
    {
        $tenant = Tenant::factory()->create([
            'plan_code' => 'core',
            'pro_expires_at' => now()->addDays(10),
        ]);

        $this->assertTrue($tenant->hasPro());

        // Expired 3 days ago (within 7-day grace period)
        $tenant->pro_expires_at = now()->subDays(3);
        $tenant->save();

        $this->assertTrue($tenant->hasPro(allowGracePeriod: true));
        $this->assertFalse($tenant->hasPro(allowGracePeriod: false));

        // Expired 10 days ago (outside 7-day grace period)
        $tenant->pro_expires_at = now()->subDays(10);
        $tenant->save();

        $this->assertFalse($tenant->hasPro(allowGracePeriod: true));
        $this->assertFalse($tenant->hasPro(allowGracePeriod: false));
    }

    public function test_has_feature_distinguishes_core_and_pro(): void
    {
        $coreTenant = Tenant::factory()->core()->create();
        $proTenant = Tenant::factory()->pro()->create();

        // Core features
        $this->assertTrue($coreTenant->hasFeature('business_site'));
        $this->assertTrue($coreTenant->hasFeature('catalog_templates'));
        $this->assertTrue($coreTenant->hasFeature('storefront_orders'));

        $this->assertTrue($proTenant->hasFeature('business_site'));
        $this->assertTrue($proTenant->hasFeature('catalog_templates'));

        // Pro features
        $this->assertFalse($coreTenant->hasFeature('custom_domain'));
        $this->assertFalse($coreTenant->hasFeature('ai_theme_design'));
        $this->assertFalse($coreTenant->hasFeature('social_scheduled_posts'));

        $this->assertTrue($proTenant->hasFeature('custom_domain'));
        $this->assertTrue($proTenant->hasFeature('ai_theme_design'));
        $this->assertTrue($proTenant->hasFeature('social_scheduled_posts'));
    }

    public function test_quota_management_and_idempotent_usage_recording(): void
    {
        $proTenant = Tenant::factory()->pro()->create();

        $this->assertEquals(3, $proTenant->quotaLimit('ai_theme_design'));
        $this->assertEquals(3, $proTenant->quotaRemaining('ai_theme_design'));

        // Record 1 usage
        $event1 = $proTenant->recordUsage('ai_theme_design', 1, 'key-1');
        $this->assertInstanceOf(UsageEvent::class, $event1);
        $this->assertEquals(2, $proTenant->quotaRemaining('ai_theme_design'));

        // Re-recording with the same key is idempotent and does not consume quota again
        $eventDuplicate = $proTenant->recordUsage('ai_theme_design', 1, 'key-1');
        $this->assertEquals($event1->id, $eventDuplicate->id);
        $this->assertEquals(2, $proTenant->quotaRemaining('ai_theme_design'));

        // Record another 2
        $proTenant->recordUsage('ai_theme_design', 2, 'key-2');
        $this->assertEquals(0, $proTenant->quotaRemaining('ai_theme_design'));

        // Attempting to consume beyond quota throws exception
        $this->expectException(\DomainException::class);
        $proTenant->recordUsage('ai_theme_design', 1, 'key-3');
    }

    public function test_reversing_usage_restores_quota(): void
    {
        $proTenant = Tenant::factory()->pro()->create();

        $proTenant->recordUsage('ai_theme_design', 1, 'reverse-key-1');
        $this->assertEquals(2, $proTenant->quotaRemaining('ai_theme_design'));

        $reversed = $proTenant->reverseUsage('reverse-key-1');
        $this->assertTrue($reversed);
        $this->assertEquals(3, $proTenant->quotaRemaining('ai_theme_design'));

        // Reversing non-existent key returns false
        $this->assertFalse($proTenant->reverseUsage('unknown-key'));
    }

    public function test_subscription_payment_new_columns_can_be_stored(): void
    {
        $tenant = Tenant::factory()->create();

        $payment = SubscriptionPayment::create([
            'tenant_id' => $tenant->id,
            'amount' => 74000.00,
            'core_amount' => 25000.00,
            'pro_amount' => 49000.00,
            'kind' => 'renewal',
            'billing_cycle' => 'monthly',
            'period_start' => now(),
            'period_end' => now()->addMonth(),
            'status' => 'approved',
            'proof_path' => 'proof.jpg',
        ]);

        $this->assertDatabaseHas('subscription_payments', [
            'id' => $payment->id,
            'kind' => 'renewal',
            'billing_cycle' => 'monthly',
            'core_amount' => 25000.00,
            'pro_amount' => 49000.00,
        ]);
    }

    public function test_enable_business_site_is_enabled_in_all_categories(): void
    {
        $categories = ['garment', 'food', 'craft', 'retail', 'cosmetic', 'homemade', 'service'];

        foreach ($categories as $cat) {
            $tenant = Tenant::factory()->create(['business_category' => $cat]);
            $this->assertTrue(
                $tenant->isModuleEnabled('enable_business_site'),
                "enable_business_site should be enabled for category {$cat}"
            );
        }
    }
}
