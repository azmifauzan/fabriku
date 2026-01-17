<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInventoryLocationRequest;
use App\Http\Requests\UpdateInventoryLocationRequest;
use App\Models\InventoryLocation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventoryLocationController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryLocation::query()
            ->withCount('inventoryItems');

        // Search functionality
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('code', 'LIKE', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $locations = $query->latest()->paginate(15);

        // Map is_active boolean to status string for frontend
        $locations->getCollection()->transform(function ($location) {
            $location->status = $location->is_active ? 'active' : 'inactive';

            return $location;
        });

        return Inertia::render('Inventory/Locations/Index', [
            'locations' => $locations,
            'filters' => $request->only(['search', 'is_active']),
        ]);
    }

    public function show(InventoryLocation $location)
    {
        $location->load([
            'inventoryItems' => function ($query) {
                $query->with('productionOrder.preparationOrder.pattern')->latest()->limit(10);
            },
        ]);

        // Map is_active boolean to status string for frontend
        $location->status = $location->is_active ? 'active' : 'inactive';

        return Inertia::render('Inventory/Locations/Show', [
            'location' => $location,
            'recentItems' => $location->inventoryItems,
            'stats' => [
                'total_items' => $location->inventoryItems()->count(),
                'total_stock' => $location->inventoryItems()->sum('current_quantity'),
                'total_value' => $location->inventoryItems()->sum(\DB::raw('current_quantity * unit_cost')),
                'low_stock_items' => $location->inventoryItems()->lowStock()->count(),
                'available_capacity' => $location->available_capacity,
                'used_capacity' => $location->used_capacity,
            ],
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('Inventory/Locations/Create');
    }

    public function store(StoreInventoryLocationRequest $request)
    {
        $location = InventoryLocation::create($request->validated());

        return redirect()
            ->route('inventory.locations.index')
            ->with('success', 'Location berhasil dibuat.');
    }

    public function edit(InventoryLocation $location)
    {
        return Inertia::render('Inventory/Locations/Edit', [
            'location' => $location,
        ]);
    }

    public function update(UpdateInventoryLocationRequest $request, InventoryLocation $location)
    {
        $location->update($request->validated());

        return redirect()
            ->route('inventory.locations.index')
            ->with('success', 'Location berhasil diupdate.');
    }

    public function destroy(InventoryLocation $location)
    {
        // Check if location has any items
        if ($location->inventoryItems()->count() > 0) {
            return back()->withErrors([
                'delete' => 'Tidak dapat menghapus lokasi yang masih berisi item.',
            ]);
        }

        $location->delete();

        return redirect()
            ->route('inventory.locations.index')
            ->with('success', 'Location berhasil dihapus.');
    }
}
