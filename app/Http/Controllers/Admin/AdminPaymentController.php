<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPayment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminPaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = SubscriptionPayment::with(['tenant', 'admin']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('tenant', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Plan filter
        if ($request->filled('plan_type')) {
            $query->where('plan_type', $request->plan_type);
        }

        $payments = $query->latest()->paginate(15)->withQueryString();

        // Get summary stats
        $stats = [
            'pending_count' => SubscriptionPayment::where('status', 'pending')->count(),
            'pending_amount' => SubscriptionPayment::where('status', 'pending')->sum('amount'),
            'approved_this_month' => SubscriptionPayment::where('status', 'approved')
                ->whereMonth('updated_at', now()->month)
                ->whereYear('updated_at', now()->year)
                ->count(),
            'approved_amount_this_month' => SubscriptionPayment::where('status', 'approved')
                ->whereMonth('updated_at', now()->month)
                ->whereYear('updated_at', now()->year)
                ->sum('amount'),
        ];

        return Inertia::render('Admin/Payments/Index', [
            'payments' => $payments,
            'filters' => $request->only(['search', 'status', 'plan_type']),
            'stats' => $stats,
        ]);
    }

    public function approve(SubscriptionPayment $payment)
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

    public function reject(Request $request, SubscriptionPayment $payment)
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
