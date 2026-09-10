<?php

namespace App\Http\Controllers;

use App\Events\PaymentProofUploaded;
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
        $isGateway = $request->input('payment_method') === 'sumopod';

        $rules = [
            'plan_type' => 'required|in:monthly,yearly',
            'payment_method' => 'sometimes|in:manual,sumopod',
        ];

        if (! $isGateway) {
            $rules['proof'] = 'required|image|max:2048'; // 2MB max
            $rules['amount'] = 'required';
        }

        $request->validate($rules);

        // Server-calculated pricing — do not trust client-submitted amount
        $serverAmount = $subscriptionService->getPlanPrice($request->plan_type);
        $durationMonths = $subscriptionService->getPlanDurationMonths($request->plan_type);
        $tenantId = auth()->user()->tenant_id;

        if ($isGateway) {
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
            $sumopodPayment = $sumopodService->createPayment([
                'order_id' => $orderId,
                'amount' => $serverAmount,
                'success_return_url' => route('subscription.index'),
                'cancel_return_url' => route('subscription.index'),
            ]);

            $payment->update([
                'provider_order_id' => $orderId,
                'provider_payment_id' => $sumopodPayment['payment_id'],
                'payment_url' => $sumopodPayment['payment_link_url'],
            ]);

            if ($request->wantsJson() || $request->header('X-Inertia')) {
                return Inertia::location($sumopodPayment['payment_link_url']);
            }

            return redirect()->away($sumopodPayment['payment_link_url']);
        }

        $path = $request->file('proof')->store("tenants/{$tenantId}/payment-proofs", config('filesystems.uploads_disk', 'fabriku_s3'));

        $payment = SubscriptionPayment::create([
            'tenant_id' => $tenantId,
            'amount' => $serverAmount,
            'proof_path' => $path,
            'status' => 'pending',
            'plan_type' => $request->plan_type,
            'duration_months' => $durationMonths,
            'payment_method' => 'manual',
        ]);

        // Notify admin via Telegram
        event(new PaymentProofUploaded($payment));

        return redirect()->back()->with('success', 'Bukti pembayaran berhasil diupload. Mohon tunggu verifikasi admin.');
    }
}
