<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInventoryItemRequest;
use App\Http\Requests\UpdateInventoryItemRequest;
use App\Models\InventoryItem;
use App\Models\InventoryItemCategory;
use App\Models\InventoryLocation;
use App\Models\ProductionOrder;
use App\Models\StockAdjustment;
use App\Services\InventoryService;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        // Filter by category
        if ($categoryId = $request->get('category_id')) {
            $query->where('category_id', $categoryId);
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

        $items = $query->paginate(self::DEFAULT_PER_PAGE);

        return Inertia::render('Inventory/Items/Index', [
            'items' => $items,
            'filters' => $request->only([
                'search', 'status', 'category_id', 'location_id', 'inventory_location_id',
                'low_stock', 'expiring_soon', 'expired',
            ]),
            'locations' => InventoryLocation::active()->orderBy('name')->get(['id', 'name', 'code', 'capacity']),
            'categories' => InventoryItemCategory::active()->orderBy('sort_order')->orderBy('name')->get(['id', 'name']),
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
            'category',
        ]);

        return Inertia::render('Inventory/Items/Show', [
            'item' => $item,
            'adjustmentTypes' => StockAdjustment::getAdjustmentTypes(),
        ]);
    }

    public function create(Request $request)
    {
        $data = $this->inventoryService->getFormDataForCreateOrEdit();

        return Inertia::render('Inventory/Items/Create', [
            'locations' => $data['locations'],
            'patterns' => $data['patterns'],
            'productionOrders' => $data['productionOrders'],
            'allowManualEntry' => true,
            'sourceTypes' => [
                'production' => 'Dari Production Order',
                'opening_balance' => 'Stock Awal / Opening Balance',
                'purchase' => 'Pembelian Langsung',
                'return' => 'Retur Customer',
            ],
            'categories' => $data['categories'],
        ]);
    }

    public function store(StoreInventoryItemRequest $request)
    {
        $data = $request->safe()->except(['image', 'locations']);
        $locations = $request->validated('locations');

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
                config('filesystems.uploads_disk', 'fabriku_s3')
            );
        }

        $locationIds = array_column($locations, 'location_id');

        // An explicit SKU is only meaningful for a single row - splitting across
        // racks must not insert the same SKU twice (unique per tenant).
        if (count($locations) > 1) {
            unset($data['sku']);
        }

        $firstItem = DB::transaction(function () use ($data, $locations, $locationIds) {
            // Lock the involved racks so two concurrent submissions can't both
            // pass the capacity check and overfill the same rack.
            InventoryLocation::whereIn('id', $locationIds)->lockForUpdate()->get();

            $createdItems = [];
            foreach ($locations as $entry) {
                $createdItems[] = InventoryItem::create([
                    ...$data,
                    'location_id' => $entry['location_id'],
                    'current_quantity' => $entry['quantity'],
                ]);
            }

            return $createdItems[0];
        });

        $message = count($locations) > 1
            ? 'Inventory item berhasil dibuat di '.count($locations).' lokasi.'
            : 'Inventory item berhasil dibuat.';

        return redirect()
            ->route('inventory.items.show', $firstItem)
            ->with('success', $message);
    }

    public function edit(InventoryItem $item)
    {
        $data = $this->inventoryService->getFormDataForCreateOrEdit($item);

        return Inertia::render('Inventory/Items/Edit', [
            'item' => $item->load('productionOrder.preparationOrder.pattern', 'category'),
            'locations' => $data['locations'],
            'patterns' => $data['patterns'],
            'productionOrders' => $data['productionOrders'],
            'allowManualEntry' => true,
            'sourceTypes' => [
                'production' => 'Dari Production Order',
                'opening_balance' => 'Stock Awal / Opening Balance',
                'purchase' => 'Pembelian Langsung',
                'return' => 'Retur Customer',
            ],
            'categories' => $data['categories'],
        ]);
    }

    public function update(UpdateInventoryItemRequest $request, InventoryItem $item)
    {
        $data = $request->safe()->except(['image']);

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($item->image_path) {
                \Storage::disk(config('filesystems.uploads_disk', 'fabriku_s3'))->delete($item->image_path);
            }

            $data['image_path'] = $request->file('image')->storePublicly(
                'tenants/'.auth()->user()->tenant_id.'/inventory',
                config('filesystems.uploads_disk', 'fabriku_s3')
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

    // QR Code features
    public function printQrCode(InventoryItem $item)
    {
        $item->load(['inventoryLocation', 'productionOrder.preparationOrder.pattern']);

        return Inertia::render('Inventory/Items/PrintQrCode', [
            'item' => $item,
        ]);
    }

    public function generateQrCode(InventoryItem $item)
    {
        // Generate QR code containing the item's SKU using BaconQrCode
        $renderer = new ImageRenderer(
            new RendererStyle(300),
            new SvgImageBackEnd
        );

        $writer = new Writer($renderer);
        $qrCode = $writer->writeString($item->sku);

        return response($qrCode)
            ->header('Content-Type', 'image/svg+xml');
    }

    public function scanLookup(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
        ]);

        $qrCode = $request->qr_code;

        // Check if it's a location URL (location QR codes contain the location's URL)
        if (str_contains($qrCode, '/inventory/locations/')) {
            preg_match('/\/inventory\/locations\/(\d+)/', $qrCode, $matches);

            if (! empty($matches[1])) {
                $location = InventoryLocation::find($matches[1]);

                if ($location) {
                    return response()->json([
                        'success' => true,
                        'redirect_url' => route('inventory.locations.show', $location),
                        'type' => 'location',
                    ]);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Lokasi tidak ditemukan.',
            ], 404);
        }

        // Otherwise treat as item SKU
        $item = InventoryItem::where('sku', $qrCode)->first();

        if (! $item) {
            return response()->json([
                'success' => false,
                'message' => 'Item atau lokasi tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'redirect_url' => route('inventory.items.show', $item),
            'type' => 'item',
        ]);
    }
}
