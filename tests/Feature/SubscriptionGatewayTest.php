<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Services\Payment\SumopodService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class SubscriptionGatewayTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_initiate_sumopod_subscription_payment(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'admin',
        ]);

        $mockSumopod = Mockery::mock(SumopodService::class);
        $mockSumopod->shouldReceive('createPayment')
            ->once()
            ->withArgs(function ($params) {
                return str_starts_with($params['order_id'], 'FAB-SUB-')
                    && $params['amount'] === 25000;
            })
            ->andReturn([
                'payment_id' => 'pay-fab-gateway-123',
                'payment_link_url' => 'https://pay.sumopod.com/checkout/123',
                'status' => 'pending',
            ]);

        $this->app->instance(SumopodService::class, $mockSumopod);

        $response = $this->actingAs($user)->post('/subscription', [
            'plan_type' => 'monthly',
            'payment_method' => 'sumopod',
            'amount' => 999, // Client sends invalid amount; server must calculate 25000
        ]);

        $response->assertRedirect('https://pay.sumopod.com/checkout/123');

        $this->assertDatabaseHas('subscription_payments', [
            'tenant_id' => $tenant->id,
            'amount' => 25000,
            'payment_method' => 'sumopod',
            'provider' => 'sumopod',
            'provider_payment_id' => 'pay-fab-gateway-123',
            'status' => 'pending',
        ]);
    }
}
