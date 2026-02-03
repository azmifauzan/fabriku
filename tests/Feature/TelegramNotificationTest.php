<?php

namespace Tests\Feature;

use App\Events\PaymentProofUploaded;
use App\Events\TenantRegistered;
use App\Models\SubscriptionPayment;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Telegram\TelegramService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Mockery;
use Tests\TestCase;

class TelegramNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_registered_event_sends_single_telegram_notification(): void
    {
        // Mock TelegramService to verify it's called only once
        $telegramMock = Mockery::mock(TelegramService::class);
        $telegramMock->shouldReceive('isConfigured')
            ->once()
            ->andReturn(true);

        $telegramMock->shouldReceive('sendAdminNotification')
            ->once() // Should be called exactly once, not twice
            ->andReturn(['success' => true]);

        $this->app->instance(TelegramService::class, $telegramMock);

        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        // Dispatch the event
        event(new TenantRegistered($tenant, $user));

        // The mock expectations will be verified automatically by Mockery
    }

    public function test_payment_proof_uploaded_event_sends_single_telegram_notification(): void
    {
        // Mock TelegramService to verify it's called only once
        $telegramMock = Mockery::mock(TelegramService::class);
        $telegramMock->shouldReceive('isConfigured')
            ->once()
            ->andReturn(true);

        $telegramMock->shouldReceive('sendAdminNotification')
            ->once() // Should be called exactly once, not twice
            ->andReturn(['success' => true]);

        $this->app->instance(TelegramService::class, $telegramMock);

        $tenant = Tenant::factory()->create();
        $payment = SubscriptionPayment::create([
            'tenant_id' => $tenant->id,
            'plan_type' => 'monthly',
            'amount' => 150000,
            'proof_path' => 'payments/test.jpg',
            'status' => 'pending',
        ]);

        // Dispatch the event
        event(new PaymentProofUploaded($payment));

        // The mock expectations will be verified automatically by Mockery
    }

    public function test_event_listeners_are_registered_once(): void
    {
        $listeners = Event::getListeners(TenantRegistered::class);

        // Should have exactly 1 listener, not 2
        $this->assertCount(1, $listeners, 'TenantRegistered should have exactly 1 listener');

        $paymentListeners = Event::getListeners(PaymentProofUploaded::class);

        // Should have exactly 1 listener, not 2
        $this->assertCount(1, $paymentListeners, 'PaymentProofUploaded should have exactly 1 listener');
    }
}
