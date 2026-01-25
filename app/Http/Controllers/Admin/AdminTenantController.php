<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AdminTenantController extends Controller
{
    /**
     * Display a listing of tenants
     */
    public function index(Request $request)
    {
        $query = Tenant::query()->withCount('users');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('business_category', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Filter by subscription plan
        if ($request->filled('plan')) {
            $query->where('subscription_plan', $request->plan);
        }

        $tenants = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('Admin/Tenants/Index', [
            'tenants' => $tenants,
            'filters' => $request->only(['search', 'status', 'plan']),
        ]);
    }

    /**
     * Show the form for creating a new tenant
     */
    public function create()
    {
        return Inertia::render('Admin/Tenants/Create');
    }

    /**
     * Store a newly created tenant
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'business_category' => ['required', 'string', 'in:garment,food,craft,cosmetic,other'],
            'subscription_plan' => ['required', 'string', 'in:trial,basic,premium,enterprise'],
            'subscription_days' => ['required', 'integer', 'min:1'],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'unique:users,email'],
            'admin_password' => ['required', 'string', 'min:8'],
        ]);

        // Create tenant
        $tenant = Tenant::create([
            'name' => $validated['name'],
            'business_category' => $validated['business_category'],
            'subscription_plan' => $validated['subscription_plan'],
            'subscription_expires_at' => now()->addDays($validated['subscription_days']),
            'is_active' => true,
        ]);

        // Create admin user for tenant
        User::create([
            'tenant_id' => $tenant->id,
            'name' => $validated['admin_name'],
            'email' => $validated['admin_email'],
            'password' => Hash::make($validated['admin_password']),
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.tenants.index')
            ->with('success', 'Tenant created successfully.');
    }

    /**
     * Display the specified tenant
     */
    public function show(Tenant $tenant)
    {
        $tenant->load(['users' => function ($query) {
            $query->latest();
        }]);

        $stats = [
            'users_count' => $tenant->users()->count(),
            'materials_count' => $tenant->materials()->count(),
            'patterns_count' => $tenant->patterns()->count(),
            'preparation_orders_count' => $tenant->preparationOrders()->count(),
            'production_orders_count' => $tenant->productionOrders()->count(),
            'sales_orders_count' => $tenant->salesOrders()->count(),
        ];

        return Inertia::render('Admin/Tenants/Show', [
            'tenant' => $tenant,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for editing the specified tenant
     */
    public function edit(Tenant $tenant)
    {
        return Inertia::render('Admin/Tenants/Edit', [
            'tenant' => $tenant,
        ]);
    }

    /**
     * Update the specified tenant
     */
    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'business_category' => ['required', 'string', 'in:garment,food,craft,cosmetic,other'],
            'subscription_plan' => ['required', 'string', 'in:trial,basic,premium,enterprise'],
            'subscription_expires_at' => ['required', 'date'],
            'is_active' => ['required', 'boolean'],
        ]);

        $tenant->update($validated);

        return redirect()->route('admin.tenants.show', $tenant)
            ->with('success', 'Tenant updated successfully.');
    }

    /**
     * Suspend the specified tenant
     */
    public function suspend(Tenant $tenant)
    {
        $tenant->update(['is_active' => false]);

        return back()->with('success', 'Tenant suspended successfully.');
    }

    /**
     * Activate the specified tenant
     */
    public function activate(Tenant $tenant)
    {
        $tenant->update(['is_active' => true]);

        return back()->with('success', 'Tenant activated successfully.');
    }

    /**
     * Remove the specified tenant
     */
    public function destroy(Tenant $tenant)
    {
        // Soft delete by setting inactive
        $tenant->update(['is_active' => false]);

        return redirect()->route('admin.tenants.index')
            ->with('success', 'Tenant deactivated successfully.');
    }
}
