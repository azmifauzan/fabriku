<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Models\PreparationOrder;
use App\Models\ProductionOrder;
use App\Models\SalesOrder;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AdminDashboardController extends Controller
{
    /**
     * Display the admin dashboard
     */
    public function index()
    {
        $stats = [
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::where('is_active', true)->count(),
            'trial_tenants' => Tenant::where('subscription_plan', 'trial')->count(),
            'total_users' => User::count(),
            'total_preparation_orders' => PreparationOrder::count(),
            'total_production_orders' => ProductionOrder::count(),
            'total_sales_orders' => SalesOrder::count(),
        ];

        // Recent tenants
        $recentTenants = Tenant::latest()
            ->take(5)
            ->get()
            ->map(fn ($tenant) => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'business_category' => $tenant->business_category,
                'subscription_plan' => $tenant->subscription_plan,
                'is_active' => $tenant->is_active,
                'created_at' => $tenant->created_at->format('Y-m-d H:i'),
            ]);

        // Tenant growth (last 7 days)
        $tenantGrowth = Tenant::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recentTenants' => $recentTenants,
            'tenantGrowth' => $tenantGrowth,
        ]);
    }
}
