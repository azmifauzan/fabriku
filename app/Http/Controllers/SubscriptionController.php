<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPayment;
use App\Models\SystemSetting;
use App\Services\Payment\SumopodService;
use App\Services\Subscription\SubscriptionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubscriptionController extends Controller
{
    public function index()
    {
        $tenant = auth()->user()->tenant;
        $settings = SystemSetting::getAllForTenant(null);
        $pendingPayment = SubscriptionPayment::where('tenant_id', $tenant->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        $history = SubscriptionPayment::where('tenant_id', $tenant->id)
            ->latest()
            ->get();

        return Inertia::render('Dashboard/Subscription/Index', [
            'tenant' => $tenant,
            'settings' => $settings,
            'pendingPayment' => $pendingPayment,
            'history' => $history,
            'server_time' => now(),
        ]);
    }

    public function store(Request $request, SubscriptionService $subscriptionService, SumopodService $sumopodService)
    {
        $request->validate([
            'plan_type' => 'required|in:monthly,yearly',
            'payment_method' => 'required|in:sumopod',
        ]);

        // Server-calculated pricing — do not trust client-submitted amount
        $serverAmount = $subscriptionService->getPlanPrice($request->plan_type);
        $durationMonths = $subscriptionService->getPlanDurationMonths($request->plan_type);
        $tenantId = auth()->user()->tenant_id;

        $payment = SubscriptionPayment::create([
            'tenant_id' => $tenantId,
            'amount' => $serverAmount,
            'proof_path' => null,
            'status' => 'pending',
            'plan_type' => $request->plan_type,
            'duration_months' => $durationMonths,
            'payment_method' => 'sumopod',
            'provider' => 'sumopod',
        ]);

        $orderId = 'FAB-SUB-'.$payment->id;
        $payment->update(['provider_order_id' => $orderId]);
        $sumopodPayment = $sumopodService->createPayment([
            'order_id' => $orderId,
            'amount' => $serverAmount,
            'success_return_url' => route('subscription.index'),
            'cancel_return_url' => route('subscription.index'),
        ]);

        $payment->update([
            'provider_payment_id' => $sumopodPayment['payment_id'],
            'payment_url' => $sumopodPayment['payment_link_url'],
            // SumoPod's own gross amount at creation time (differs from `amount` when
            // "charge fee to customer" is on) — the webhook is verified against this, not
            // `amount`, since that setting can make them diverge legitimately.
            'provider_amount' => $sumopodPayment['amount'] ?? null,
            'provider_fee' => $sumopodPayment['fee'] ?? null,
        ]);

        if ($request->wantsJson() || $request->header('X-Inertia')) {
            return Inertia::location($sumopodPayment['payment_link_url']);
        }

        return redirect()->away($sumopodPayment['payment_link_url']);
    }
}
