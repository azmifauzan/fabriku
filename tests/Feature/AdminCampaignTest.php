<?php

use App\Mail\FeatureCampaignEmail;
use App\Models\AdminUser;
use App\Models\CampaignLog;
use App\Models\SystemSetting;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Campaign\CampaignLlmService;
use App\Services\Campaign\FabrikuFeatureCatalog;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

function campaignActingAsAdmin(): AdminUser
{
    $admin = AdminUser::factory()->create();
    test()->actingAs($admin, 'admin');

    return $admin;
}

test('admin can view campaign tracking page with statistics and settings', function () {
    campaignActingAsAdmin();

    $tenant = Tenant::factory()->create(['business_category' => 'garment']);
    $user = User::factory()->create(['tenant_id' => $tenant->id, 'role' => 'admin']);

    CampaignLog::create([
        'batch_id' => 'batch_test_1',
        'tenant_id' => $tenant->id,
        'user_id' => $user->id,
        'recipient_email' => $user->email,
        'recipient_name' => $user->name,
        'business_category' => 'garment',
        'feature_key' => 'pattern_bom',
        'feature_name' => 'Pattern & BOM Pakaian',
        'subject' => 'Tips Garment: Pattern & BOM di Fabriku',
        'content_html' => '<p>Konten email</p>',
        'status' => 'sent',
        'sent_at' => now(),
    ]);

    $response = $this->get(route('admin.campaigns.index'));

    $response->assertOk()->assertInertia(fn ($page) => $page
        ->component('Admin/Campaigns/Index')
        ->has('logs.data', 1)
        ->has('stats')
        ->has('settings')
        ->has('features')
        ->has('categories')
    );
});

test('admin can update LLM and campaign settings', function () {
    campaignActingAsAdmin();

    $response = $this->post(route('admin.campaigns.settings.update'), [
        'llm_base_url' => 'https://api.deepseek.com/v1',
        'llm_api_key' => 'sk-deepseek-test-key',
        'llm_model' => 'deepseek-chat',
        'llm_temperature' => 0.8,
        'llm_max_tokens' => 1500,
        'campaign_is_active' => true,
        'campaign_system_prompt' => 'Custom system prompt',
    ]);

    $response->assertRedirect()->assertSessionHas('success');

    expect(SystemSetting::get('llm_base_url'))->toBe('https://api.deepseek.com/v1');
    expect(SystemSetting::get('llm_api_key'))->toBe('sk-deepseek-test-key');
    expect(SystemSetting::get('llm_model'))->toBe('deepseek-chat');
    expect(SystemSetting::get('llm_temperature'))->toBe(0.8);
    expect(SystemSetting::get('llm_max_tokens'))->toBe(1500.0);
    expect(SystemSetting::get('campaign_is_active'))->toBe(true);
    expect(SystemSetting::get('campaign_system_prompt'))->toBe('Custom system prompt');
});

test('admin can test LLM connection with mock response', function () {
    campaignActingAsAdmin();

    Http::fake([
        'https://api.openai.com/v1/chat/completions' => Http::response([
            'choices' => [
                [
                    'message' => [
                        'content' => 'Konfirmasi koneksi LLM OpenAI-compatible untuk Fabriku berhasil.',
                    ],
                ],
            ],
        ], 200),
    ]);

    $response = $this->postJson(route('admin.campaigns.test-llm'), [
        'llm_base_url' => 'https://api.openai.com/v1',
        'llm_api_key' => 'sk-test-key',
        'llm_model' => 'gpt-4o-mini',
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'model' => 'gpt-4o-mini',
        ]);
});

test('admin can send sample test email', function () {
    campaignActingAsAdmin();
    Mail::fake();

    $response = $this->post(route('admin.campaigns.send-test-email'), [
        'email' => 'tester@example.com',
        'category' => 'food',
        'feature_key' => 'recipe_bom_hpp',
    ]);

    $response->assertRedirect()->assertSessionHas('success');

    Mail::assertQueued(FeatureCampaignEmail::class, function ($mail) {
        return $mail->hasTo('tester@example.com');
    });

    expect(CampaignLog::where('recipient_email', 'tester@example.com')->exists())->toBeTrue();
});

test('admin can trigger batch campaign sending immediately', function () {
    campaignActingAsAdmin();
    Mail::fake();

    $tenant = Tenant::factory()->create([
        'business_category' => 'retail',
        'is_active' => true,
    ]);

    $adminUser = User::factory()->create([
        'tenant_id' => $tenant->id,
        'role' => 'admin',
        'is_active' => true,
    ]);

    $response = $this->post(route('admin.campaigns.send-batch'));

    $response->assertRedirect()->assertSessionHas('success');

    Mail::assertQueued(FeatureCampaignEmail::class, function ($mail) use ($adminUser) {
        return $mail->hasTo($adminUser->email);
    });

    expect(CampaignLog::where('recipient_email', $adminUser->email)->exists())->toBeTrue();
});

test('scheduled command campaign:send-weekly dispatches emails and logs results', function () {
    Mail::fake();

    $tenantGarment = Tenant::factory()->create([
        'name' => 'Konveksi Berkah',
        'business_category' => 'garment',
        'is_active' => true,
    ]);

    $userGarment = User::factory()->create([
        'tenant_id' => $tenantGarment->id,
        'role' => 'admin',
        'is_active' => true,
    ]);

    $tenantFood = Tenant::factory()->create([
        'name' => 'Bakery Lezat',
        'business_category' => 'food',
        'is_active' => true,
    ]);

    $userFood = User::factory()->create([
        'tenant_id' => $tenantFood->id,
        'role' => 'admin',
        'is_active' => true,
    ]);

    Artisan::call('campaign:send-weekly');

    Mail::assertQueued(FeatureCampaignEmail::class, function ($mail) use ($userGarment) {
        return $mail->hasTo($userGarment->email);
    });

    Mail::assertQueued(FeatureCampaignEmail::class, function ($mail) use ($userFood) {
        return $mail->hasTo($userFood->email);
    });

    $logs = CampaignLog::whereIn('recipient_email', [$userGarment->email, $userFood->email])->get();
    expect($logs)->toHaveCount(2);
    expect($logs->where('recipient_email', $userGarment->email)->first()->business_category)->toBe('garment');
    expect($logs->where('recipient_email', $userFood->email)->first()->business_category)->toBe('food');
});

test('campaign catalog contains features for all business categories', function () {
    $catalog = FabrikuFeatureCatalog::all();

    expect($catalog)->toHaveKeys(['garment', 'food', 'craft', 'retail', 'service', 'general']);
    expect($catalog['garment'])->not->toBeEmpty();
    expect($catalog['food'])->not->toBeEmpty();
    expect($catalog['retail'])->not->toBeEmpty();
});

test('demo tenants and demo admin users are excluded from campaign emails', function () {
    Mail::fake();

    // Demo tenant
    $demoTenant = Tenant::factory()->create([
        'name' => 'Konveksi Fabriku',
        'is_active' => true,
    ]);

    $demoUser = User::factory()->create([
        'tenant_id' => $demoTenant->id,
        'email' => 'admin@konveksi.com',
        'role' => 'admin',
        'is_active' => true,
    ]);

    // Regular tenant
    $realTenant = Tenant::factory()->create([
        'name' => 'Usaha Nyata Berkah',
        'is_active' => true,
    ]);

    $realUser = User::factory()->create([
        'tenant_id' => $realTenant->id,
        'email' => 'pemilik@usahanyata.com',
        'role' => 'admin',
        'is_active' => true,
    ]);

    Artisan::call('campaign:send-weekly');

    Mail::assertQueued(FeatureCampaignEmail::class, function ($mail) use ($realUser) {
        return $mail->hasTo($realUser->email);
    });

    Mail::assertNotQueued(FeatureCampaignEmail::class, function ($mail) use ($demoUser) {
        return $mail->hasTo($demoUser->email);
    });

    expect(CampaignLog::where('recipient_email', $demoUser->email)->exists())->toBeFalse();
    expect(CampaignLog::where('recipient_email', $realUser->email)->exists())->toBeTrue();
});

