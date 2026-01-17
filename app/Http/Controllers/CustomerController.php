<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('code', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // Filter by type
        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $customers = $query->latest()->paginate(15);

        return Inertia::render('Customers/Index', [
            'customers' => $customers,
            'filters' => $request->only(['search', 'type', 'is_active']),
        ]);
    }

    public function show(Customer $customer)
    {
        $customer->load(['salesOrders' => function ($query) {
            $query->latest()->limit(10);
        }]);

        return Inertia::render('Customers/Show', [
            'customer' => $customer,
            'recentOrders' => $customer->salesOrders,
            'stats' => [
                'total_orders' => $customer->salesOrders()->count(),
                'total_revenue' => $customer->salesOrders()->where('status', 'completed')->sum('total_amount'),
                'pending_orders' => $customer->salesOrders()->whereIn('status', ['draft', 'confirmed', 'processing'])->count(),
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Customers/Create');
    }

    public function store(StoreCustomerRequest $request)
    {
        $customer = Customer::create(array_merge($request->validated(), [
            'tenant_id' => auth()->user()->tenant_id,
        ]));

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer berhasil ditambahkan.');
    }

    public function edit(Customer $customer)
    {
        return Inertia::render('Customers/Edit', [
            'customer' => $customer,
        ]);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $customer->update($request->validated());

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer berhasil diupdate.');
    }

    public function destroy(Customer $customer)
    {
        // Check if customer has orders
        if ($customer->salesOrders()->count() > 0) {
            return back()->withErrors([
                'delete' => 'Tidak dapat menghapus customer yang memiliki sales order.',
            ]);
        }

        $customer->delete();

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer berhasil dihapus.');
    }
}
