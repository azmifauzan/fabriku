<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInventoryItemRequest;
use App\Http\Requests\UpdateInventoryItemRequest;
use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\Pattern;
use App\Models\ProductionOrder;
use App\Models\StockAdjustment;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventoryItemController extends Controller
{
    public function __construct(private InventoryService $inventoryService) {}

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
        // Removed: quality_grade filtering

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
                'low_stock', 'expiring_soon', 'expired',
            ]),
            'locations' => InventoryLocation::active()->orderBy('name')->get(['id', 'name', 'code', 'capacity']),
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
            'adjustmentTypes' => StockAdjustment::getAdjustmentTypes(),
        ]);
    }

    public function create(Request $request)
    {
        $locations = InventoryLocation::active()->orderBy('name')->get(['id', 'name', 'code', 'capacity']);
        $patterns = Pattern::orderBy('name')->get(['id', 'name', 'code', 'output_quantity']);
        // Only show completed or sent production orders that don't have inventory items yet
        $productionOrders = ProductionOrder::whereIn('status', ['completed', 'sent'])
            ->whereDoesntHave('inventoryItems')
            ->with(['preparationOrder' => function ($query) {
                $query->select('id', 'pattern_id', 'output_quantity', 'output_unit')
                    ->with(['materialUsages.materialReceipt']);
            }, 'preparationOrder.pattern'])
            ->orderByRaw('COALESCE(completed_date, estimated_completion_date) DESC')
            ->get(['id', 'order_number', 'preparation_order_id', 'labor_cost', 'completed_date', 'estimated_completion_date', 'status'])
            ->map(function ($order) {
                $materialCost = $order->preparationOrder->materialUsages->sum(function ($usage) {
                    return $usage->quantity * ($usage->materialReceipt->price_per_unit ?? 0);
                });
                $order->material_cost = $materialCost;

                return $order;
            });

        return Inertia::render('Inventory/Items/Create', [
            'locations' => $locations,
            'patterns' => $patterns,
            'productionOrders' => $productionOrders,
            'allowManualEntry' => true,
            'sourceTypes' => [
                'production' => 'Dari Production Order',
                'opening_balance' => 'Stock Awal / Opening Balance',
                'purchase' => 'Pembelian Langsung',
                'return' => 'Retur Customer',
            ],
            'categories' => [
                'garment' => 'Garment',
                'food' => 'Makanan',
                'craft' => 'Kerajinan',
                'other' => 'Lainnya',
            ],
        ]);
    }

    public function store(StoreInventoryItemRequest $request)
    {
        $data = $request->safe()->except(['image']);

        // Set source_type based on whether production_order_id is present
        if (empty($data['production_order_id'])) {
            $data['source_type'] = $data['source_type'] ?? 'opening_balance';
        } else {
            $data['source_type'] = 'production';

            // Get product_name from production order's pattern if not provided
            if (empty($data['product_name'])) {
                $productionOrder = ProductionOrder::with('preparationOrder.pattern')
                    ->find($data['production_order_id']);
                if ($productionOrder && $productionOrder->preparationOrder && $productionOrder->preparationOrder->pattern) {
                    $data['product_name'] = $productionOrder->preparationOrder->pattern->name;
                }
            }
        }

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->storePublicly(
                'tenants/'.auth()->user()->tenant_id.'/inventory',
                'fabriku_s3'
            );
        }

        $item = InventoryItem::create($data);

        // Note: Opening balance is now reflected in the initial current_quantity
        // Stock adjustments will track subsequent changes to stock
        // The source_type field indicates how this item was initially created

        return redirect()
            ->route('inventory.items.show', $item)
            ->with('success', 'Inventory item berhasil dibuat.');
    }

    public function edit(InventoryItem $inventoryItem)
    {
        $locations = InventoryLocation::active()->orderBy('name')->get(['id', 'name', 'code', 'capacity']);
        $patterns = Pattern::orderBy('name')->get(['id', 'name', 'code', 'output_quantity']);
        // Only show completed or sent production orders that don't have inventory items yet
        // Include current item's production order
        $productionOrders = ProductionOrder::whereIn('status', ['completed', 'sent'])
            ->where(function ($query) use ($inventoryItem) {
                $query->whereDoesntHave('inventoryItems')
                    ->orWhere('id', $inventoryItem->production_order_id);
            })
            ->with(['preparationOrder' => function ($query) {
                $query->select('id', 'pattern_id', 'output_quantity', 'output_unit')
                    ->with(['materialUsages.materialReceipt']);
            }, 'preparationOrder.pattern'])
            ->orderByRaw('COALESCE(completed_date, estimated_completion_date) DESC')
            ->get(['id', 'order_number', 'preparation_order_id', 'labor_cost', 'completed_date', 'estimated_completion_date', 'status'])
            ->map(function ($order) {
                $materialCost = $order->preparationOrder->materialUsages->sum(function ($usage) {
                    return $usage->quantity * ($usage->materialReceipt->price_per_unit ?? 0);
                });
                $order->material_cost = $materialCost;

                return $order;
            });

        return Inertia::render('Inventory/Items/Edit', [
            'item' => $inventoryItem->load('productionOrder.preparationOrder.pattern'),
            'locations' => $locations,
            'patterns' => $patterns,
            'productionOrders' => $productionOrders,
            'allowManualEntry' => true,
            'sourceTypes' => [
                'production' => 'Dari Production Order',
                'opening_balance' => 'Stock Awal / Opening Balance',
                'purchase' => 'Pembelian Langsung',
                'return' => 'Retur Customer',
            ],
            'categories' => [
                'garment' => 'Garment',
                'food' => 'Makanan',
                'craft' => 'Kerajinan',
                'other' => 'Lainnya',
            ],
        ]);
    }

    public function update(UpdateInventoryItemRequest $request, InventoryItem $item)
    {
        $data = $request->safe()->except(['image']);

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($item->image_path) {
                \Storage::disk('fabriku_s3')->delete($item->image_path);
            }

            $data['image_path'] = $request->file('image')->storePublicly(
                'tenants/'.auth()->user()->tenant_id.'/inventory',
                'fabriku_s3'
            );
        }

        $item->update($data);

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
    public function adjustStock(Request $request, InventoryItem $item)
    {
        $rules = [
            'type' => 'required|in:add,subtract,set',
            'quantity' => 'required|integer|min:0',
            'adjustment_type' => 'required|in:'.implode(',', array_keys(StockAdjustment::getAdjustmentTypes())),
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ];

        // Add custom validation for subtract to prevent negative stock
        if ($request->type === 'subtract' && $request->quantity > $item->current_quantity) {
            $rules['quantity'] = 'required|integer|min:0|max:'.$item->current_quantity;
        }

        $request->validate($rules, [
            'quantity.max' => 'Jumlah pengurangan tidak boleh melebihi stock saat ini ('.$item->current_quantity.').',
        ]);

        try {
            $adjustment = $this->inventoryService->adjustStock(
                $item,
                $request->type,
                $request->quantity,
                $request->adjustment_type,
                $request->reason,
                $request->notes
            );

            return back()->with('success', 'Stock berhasil disesuaikan.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function adjustmentHistory(InventoryItem $item)
    {
        $adjustments = $this->inventoryService->getAdjustmentHistory($item);

        return Inertia::render('Inventory/Items/AdjustmentHistory', [
            'item' => $item->load(['inventoryLocation', 'productionOrder.preparationOrder.pattern']),
            'adjustments' => $adjustments,
            'adjustmentTypes' => StockAdjustment::getAdjustmentTypes(),
        ]);
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
