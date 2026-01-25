<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index()
    {
        $tenant = auth()->user()->tenant;
        $settings = \App\Models\SystemSetting::getAllForTenant(null);
        
        $pendingPayment = \App\Models\SubscriptionPayment::where('tenant_id', $tenant->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        $history = \App\Models\SubscriptionPayment::where('tenant_id', $tenant->id)
            ->latest()
            ->get();

        return \Inertia\Inertia::render('Dashboard/Subscription/Index', [
            'tenant' => $tenant,
            'settings' => $settings,
            'pendingPayment' => $pendingPayment,
            'history' => $history,
            'server_time' => now(),
        ]);
    }

    public function store(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'plan_type' => 'required|in:monthly,yearly',
            'proof' => 'required|image|max:2048', // 2MB max
            'amount' => 'required|numeric',
        ]);

        $path = $request->file('proof')->store('payment-proofs', 'public');

        \App\Models\SubscriptionPayment::create([
            'tenant_id' => auth()->user()->tenant_id,
            'amount' => $request->amount,
            'proof_path' => $path,
            'status' => 'pending',
            'plan_type' => $request->plan_type,
            'duration_months' => $request->plan_type === 'yearly' ? 12 : 1,
        ]);

        return redirect()->back()->with('success', 'Bukti pembayaran berhasil diupload. Mohon tunggu verifikasi admin.');
    }
}
