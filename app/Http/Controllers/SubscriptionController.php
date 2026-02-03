<?php

namespace App\Http\Controllers;

use App\Events\PaymentProofUploaded;
use App\Models\SubscriptionPayment;
use App\Models\SystemSetting;
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

    public function store(Request $request)
    {
        $request->validate([
            'plan_type' => 'required|in:monthly,yearly',
            'proof' => 'required|image|max:2048', // 2MB max
            'amount' => 'required|numeric',
        ]);

        $tenantId = auth()->user()->tenant_id;
        $path = $request->file('proof')->store("tenants/{$tenantId}/payment-proofs", 'fabriku_s3');

        $payment = SubscriptionPayment::create([
            'tenant_id' => $tenantId,
            'amount' => $request->amount,
            'proof_path' => $path,
            'status' => 'pending',
            'plan_type' => $request->plan_type,
            'duration_months' => $request->plan_type === 'yearly' ? 12 : 1,
        ]);

        // Notify admin via Telegram
        event(new PaymentProofUploaded($payment));

        return redirect()->back()->with('success', 'Bukti pembayaran berhasil diupload. Mohon tunggu verifikasi admin.');
    }
}
