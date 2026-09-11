<?php

namespace App\Services\Subscription;

use App\Models\SubscriptionPayment;
use App\Models\SystemSetting;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    public const DEFAULT_MONTHLY_PRICE = 25000;

    public const DEFAULT_YEARLY_PRICE = 250000;

    /**
     * Calculate subscription price purely on server side.
     */
    public function getPlanPrice(string $planType): int
    {
        if ($planType === 'yearly') {
            return (int) SystemSetting::get('membership_price_yearly', self::DEFAULT_YEARLY_PRICE);
        }

        return (int) SystemSetting::get('membership_price_monthly', self::DEFAULT_MONTHLY_PRICE);
    }

    /**
     * Get duration in months for a given plan type.
     */
    public function getPlanDurationMonths(string $planType): int
    {
        return $planType === 'yearly' ? 12 : 1;
    }

    /**
     * Activate or extend tenant subscription using row locks and transactions.
     * Shared by admin approval and webhook settlement to prevent double activation.
     */
    public function activateSubscription(SubscriptionPayment $payment, ?int $adminId = null): SubscriptionPayment
    {
        return DB::transaction(function () use ($payment, $adminId): SubscriptionPayment {
            /** @var SubscriptionPayment $lockedPayment */
            $lockedPayment = SubscriptionPayment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();

            // If already approved, return immediately to guarantee idempotency
            if ($lockedPayment->status === 'approved') {
                return $lockedPayment;
            }

            /** @var Tenant $tenant */
            $tenant = Tenant::query()->whereKey($lockedPayment->tenant_id)->lockForUpdate()->firstOrFail();

            $currentExpiry = $tenant->subscription_expires_at && $tenant->subscription_expires_at->isFuture()
                ? $tenant->subscription_expires_at
                : now();

            $newExpiry = $currentExpiry->copy()->addMonths($lockedPayment->duration_months);

            $tenant->update([
                'subscription_plan' => 'full',
                'subscription_expires_at' => $newExpiry,
                'is_active' => true,
            ]);

            $lockedPayment->update([
                'status' => 'approved',
                'paid_at' => now(),
                'admin_id' => $adminId ?? $lockedPayment->admin_id,
            ]);

            return $lockedPayment->fresh(['tenant']);
        });
    }

    /**
     * Mark subscription payment as failed or expired without affecting tenant subscription.
     */
    public function markFailed(SubscriptionPayment $payment, string $eventType, array $payload = []): SubscriptionPayment
    {
        return DB::transaction(function () use ($payment, $eventType, $payload): SubscriptionPayment {
            /** @var SubscriptionPayment $lockedPayment */
            $lockedPayment = SubscriptionPayment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();

            // Completed / approved status is final - never downgrade active subscription
            if ($lockedPayment->status === 'approved') {
                return $lockedPayment;
            }

            if ($lockedPayment->status === 'pending') {
                $targetStatus = $eventType === 'payment.expired' ? 'expired' : 'failed';
                $lockedPayment->update([
                    'status' => $targetStatus,
                    'provider_payload' => $payload,
                ]);
            }

            return $lockedPayment->fresh(['tenant']);
        });
    }
}
