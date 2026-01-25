<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminPaymentController extends Controller
{
    public function index()
    {
        $payments = \App\Models\SubscriptionPayment::with(['tenant', 'admin'])
            ->latest()
            ->get();

        return \Inertia\Inertia::render('Admin/Payments/Index', [
            'payments' => $payments,
        ]);
    }

    public function approve(\App\Models\SubscriptionPayment $payment)
    {
        $payment->load('tenant');
        
        \Illuminate\Support\Facades\DB::transaction(function () use ($payment) {
            // Update Payment
            $payment->update([
                'status' => 'approved',
                'admin_id' => auth()->id(),
            ]);

            // Update Tenant
            $tenant = $payment->tenant;
            
            $currentExpiry = $tenant->subscription_expires_at && $tenant->subscription_expires_at->isFuture()
                ? $tenant->subscription_expires_at
                : now();

            $tenant->update([
                'subscription_plan' => 'full',
                'subscription_expires_at' => $currentExpiry->addMonths($payment->duration_months),
                'is_active' => true,
            ]);
        });

        return redirect()->back()->with('success', 'Pembayaran disetujui. Subscription tenant diperpanjang.');
    }

    public function reject(\Illuminate\Http\Request $request, \App\Models\SubscriptionPayment $payment)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $payment->update([
            'status' => 'rejected',
            'admin_id' => auth()->id(),
            'rejection_reason' => $request->reason,
        ]);

        return redirect()->back()->with('success', 'Pembayaran ditolak.');
    }
}
