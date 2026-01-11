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
            ->with(['parent', 'children'])
            ->withCount('inventoryItems');

        // Search functionality
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'LIKE', "%{$search}%")
                    ->orWhere('name', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Filter by type
        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        // Filter by status
        if ($request->has('status')) {
            $status = $request->boolean('status');
            $query->where('is_active', $status);
        }

        // Filter by parent (hierarchy level)
        if ($request->has('parent_id')) {
            $parentId = $request->get('parent_id');
            if ($parentId === 'null') {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $parentId);
            }
        }

        $locations = $query->latest()->paginate(15);

        return Inertia::render('Inventory/Locations/Index', [
            'locations' => $locations,
            'filters' => $request->only(['search', 'type', 'status', 'parent_id']),
            'types' => [
                'warehouse' => 'Warehouse',
                'room' => 'Room',
                'zone' => 'Zone',
                'rack' => 'Rack',
                'shelf' => 'Shelf',
            ],
        ]);
    }

    public function show(InventoryLocation $inventoryLocation)
    {
        $inventoryLocation->load([
            'parent',
            'children' => function ($query) {
                $query->withCount('inventoryItems');
            },
            'inventoryItems' => function ($query) {
                $query->with('pattern')->latest()->limit(10);
            },
        ]);

        return Inertia::render('Inventory/Locations/Show', [
            'location' => $inventoryLocation,
            'recentItems' => $inventoryLocation->inventoryItems,
            'stats' => [
                'total_items' => $inventoryLocation->inventoryItems()->count(),
                'total_stock' => $inventoryLocation->inventoryItems()->sum('current_stock'),
                'total_value' => $inventoryLocation->inventoryItems()->sum(\DB::raw('current_stock * unit_cost')),
                'low_stock_items' => $inventoryLocation->inventoryItems()->lowStock()->count(),
            ],
        ]);
    }

    public function create(Request $request)
    {
        $parentLocations = InventoryLocation::active()
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'type']);

        return Inertia::render('Inventory/Locations/Create', [
            'parentLocations' => $parentLocations,
            'types' => [
                'warehouse' => 'Warehouse',
                'room' => 'Room',
                'zone' => 'Zone',
                'rack' => 'Rack',
                'shelf' => 'Shelf',
            ],
        ]);
    }

    public function store(StoreInventoryLocationRequest $request)
    {
        $location = InventoryLocation::create($request->validated());

        return redirect()
            ->route('inventory-locations.show', $location)
            ->with('success', 'Location berhasil dibuat.');
    }

    public function edit(InventoryLocation $inventoryLocation)
    {
        $parentLocations = InventoryLocation::active()
            ->where('id', '!=', $inventoryLocation->id)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'type']);

        return Inertia::render('Inventory/Locations/Edit', [
            'location' => $inventoryLocation,
            'parentLocations' => $parentLocations,
            'types' => [
                'warehouse' => 'Warehouse',
                'room' => 'Room',
                'zone' => 'Zone',
                'rack' => 'Rack',
                'shelf' => 'Shelf',
            ],
        ]);
    }

    public function update(UpdateInventoryLocationRequest $request, InventoryLocation $inventoryLocation)
    {
        $inventoryLocation->update($request->validated());

        return redirect()
            ->route('inventory-locations.show', $inventoryLocation)
            ->with('success', 'Location berhasil diperbarui.');
    }

    public function destroy(InventoryLocation $inventoryLocation)
    {
        // Check if location has items
        if ($inventoryLocation->inventoryItems()->count() > 0) {
            return back()->with('error', 'Cannot delete location with existing inventory items.');
        }

        // Check if location has children
        if ($inventoryLocation->children()->count() > 0) {
            return back()->with('error', 'Cannot delete location with sub-locations.');
        }

        $inventoryLocation->delete();

        return redirect()
            ->route('inventory-locations.index')
            ->with('success', 'Location berhasil dihapus.');
    }

    // API endpoints for Vue components
    public function getHierarchy(Request $request)
    {
        $locations = InventoryLocation::with(['children' => function ($query) {
            $query->active()->with('children');
        }])
            ->rootLocations()
            ->active()
            ->orderBy('name')
            ->get();

        return response()->json($locations);
    }

    public function getAvailableLocations(Request $request)
    {
        $locations = InventoryLocation::available()
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'type', 'capacity']);

        return response()->json($locations);
    }
}
