<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInventoryItemRequest;
use App\Http\Requests\UpdateInventoryItemRequest;
use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\Pattern;
use App\Models\ProductionOrder;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventoryItemController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryItem::query()
            ->with(['inventoryLocation', 'productionOrder.preparationOrder.pattern']);

        // Search functionality
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'LIKE', "%{$search}%")
                    ->orWhere('product_name', 'LIKE', "%{$search}%")
                    ->orWhere('product_code', 'LIKE', "%{$search}%");
            });
        }

        // Filter by status
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // Filter by location
        $locationId = $request->get('location_id') ?? $request->get('inventory_location_id');
        if ($locationId) {
            $query->where('location_id', $locationId);
        }

        // Filter by quality grade (mainly for garment)
        if ($qualityGrade = $request->get('quality_grade')) {
            $query->where('quality_grade', $qualityGrade);
        }

        // Special filters
        if ($request->get('low_stock')) {
            $query->lowStock();
        }

        if ($request->get('expiring_soon')) {
            $query->expiring(7); // Items expiring in next 7 days
        }

        if ($request->get('expired')) {
            $query->expired();
        }

        // Sort
        $sortBy = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        $items = $query->paginate(20);

        return Inertia::render('Inventory/Items/Index', [
            'items' => $items,
            'filters' => $request->only([
                'search', 'status', 'location_id', 'inventory_location_id',
                'quality_grade', 'low_stock', 'expiring_soon', 'expired',
            ]),
            'locations' => InventoryLocation::active()->orderBy('name')->get(['id', 'name', 'zone', 'rack']),
            'stats' => [
                'total_items' => InventoryItem::count(),
                'total_stock' => InventoryItem::sum('current_quantity'),
                'low_stock_count' => InventoryItem::lowStock()->count(),
                'expiring_soon_count' => InventoryItem::expiring(7)->count(),
                'expired_count' => InventoryItem::expired()->count(),
            ],
        ]);
    }

    public function show(InventoryItem $item)
    {
        $item->load([
            'inventoryLocation',
            'productionOrder.preparationOrder.pattern',
            'tenant',
        ]);

        return Inertia::render('Inventory/Items/Show', [
            'item' => $item,
        ]);
    }

    public function create(Request $request)
    {
        $locations = InventoryLocation::active()->orderBy('name')->get(['id', 'name', 'zone', 'rack']);
        $patterns = Pattern::orderBy('name')->get(['id', 'name', 'code', 'product_type']);
        // Only show completed or sent production orders for inventory
        $productionOrders = ProductionOrder::whereIn('status', ['completed', 'sent'])
            ->with(['preparationOrder.pattern'])
            ->orderBy('completed_date', 'desc')
            ->get(['id', 'order_number', 'preparation_order_id', 'quantity_good', 'labor_cost', 'completed_date', 'estimated_completion_date', 'status']);

        return Inertia::render('Inventory/Items/Create', [
            'locations' => $locations,
            'patterns' => $patterns,
            'productionOrders' => $productionOrders,
            'categories' => [
                'garment' => 'Garment',
                'food' => 'Makanan',
                'craft' => 'Kerajinan',
                'other' => 'Lainnya',
            ],
            'qualityGrades' => [
                'A' => 'Grade A (Perfect)',
                'B' => 'Grade B (Minor defects)',
                'C' => 'Grade C (Major defects)',
                'reject' => 'Reject (Not saleable)',
            ],
        ]);
    }

    public function store(StoreInventoryItemRequest $request)
    {
        $item = InventoryItem::create($request->validated());

        return redirect()
            ->route('inventory.items.show', $item)
            ->with('success', 'Inventory item berhasil dibuat.');
    }

    public function edit(InventoryItem $inventoryItem)
    {
        $locations = InventoryLocation::active()->orderBy('name')->get(['id', 'name', 'zone', 'rack']);
        $patterns = Pattern::orderBy('name')->get(['id', 'name', 'code', 'product_type']);
        // Only show completed or sent production orders for inventory
        $productionOrders = ProductionOrder::whereIn('status', ['completed', 'sent'])
            ->with(['preparationOrder.pattern'])
            ->orderBy('completed_date', 'desc')
            ->get(['id', 'order_number', 'preparation_order_id', 'quantity_good', 'labor_cost', 'completed_date', 'estimated_completion_date', 'status']);

        return Inertia::render('Inventory/Items/Edit', [
            'item' => $inventoryItem->load('productionOrder.preparationOrder.pattern'),
            'locations' => $locations,
            'patterns' => $patterns,
            'productionOrders' => $productionOrders,
            'categories' => [
                'garment' => 'Garment',
                'food' => 'Makanan',
                'craft' => 'Kerajinan',
                'other' => 'Lainnya',
            ],
            'qualityGrades' => [
                'A' => 'Grade A (Perfect)',
                'B' => 'Grade B (Minor defects)',
                'C' => 'Grade C (Major defects)',
                'reject' => 'Reject (Not saleable)',
            ],
        ]);
    }

    public function update(UpdateInventoryItemRequest $request, InventoryItem $item)
    {
        $item->update($request->validated());

        return redirect()
            ->route('inventory.items.index')
            ->with('success', 'Inventory item berhasil diperbarui.');
    }

    public function destroy(InventoryItem $item)
    {
        // Check if item has reserved stock (pending sales)
        if ($item->reserved_stock > 0) {
            return back()->with('error', 'Cannot delete item with reserved stock.');
        }

        $item->delete();

        return redirect()
            ->route('inventory.items.index')
            ->with('success', 'Inventory item berhasil dihapus.');
    }

    // Stock management endpoints
    public function adjustStock(Request $request, InventoryItem $inventoryItem)
    {
        $request->validate([
            'type' => 'required|in:add,subtract,set',
            'quantity' => 'required|integer|min:0',
            'reason' => 'required|string|max:255',
        ]);

        $oldStock = $inventoryItem->current_stock;

        switch ($request->type) {
            case 'add':
                $inventoryItem->increment('current_quantity', $request->quantity);
                break;
            case 'subtract':
                $newStock = max(0, $oldStock - $request->quantity);
                $inventoryItem->update(['current_quantity' => $newStock]);
                break;
            case 'set':
                $inventoryItem->update(['current_quantity' => $request->quantity]);
                break;
        }

        // Log stock adjustment (would be implemented with audit trail)
        // StockMovement::create([...]);

        return back()->with('success', 'Stock berhasil disesuaikan.');
    }

    public function reserve(Request $request, InventoryItem $inventoryItem)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        if (! $inventoryItem->reserveStock($request->quantity)) {
            return back()->with('error', 'Stock tidak mencukupi untuk reservasi.');
        }

        return back()->with('success', 'Stock berhasil direservasi.');
    }

    public function releaseReserve(Request $request, InventoryItem $inventoryItem)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        if (! $inventoryItem->releaseReservedStock($request->quantity)) {
            return back()->with('error', 'Reserved stock tidak mencukupi.');
        }

        return back()->with('success', 'Reserved stock berhasil dilepaskan.');
    }

    // Move item to different location
    public function move(Request $request, InventoryItem $inventoryItem)
    {
        $request->validate([
            'location_id' => 'required|exists:inventory_locations,id',
            'reason' => 'nullable|string|max:255',
        ]);

        $oldLocation = $inventoryItem->inventoryLocation;
        $inventoryItem->update(['location_id' => $request->location_id]);

        // Log location change
        // StockMovement::create([...]);

        return back()->with('success', 'Item berhasil dipindahkan.');
    }
}
