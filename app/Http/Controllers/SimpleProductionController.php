<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\InventoryItemCategory;
use App\Models\InventoryLocation;
use App\Models\Material;
use App\Models\Pattern;
use App\Models\PreparationOrder;
use App\Models\StockAdjustment;
use App\Services\MaterialStockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class SimpleProductionController extends Controller
{
    public function __construct(
        protected MaterialStockService $stockService
    ) {}

    public function index(Request $request): Response
    {
        $tenantId = auth()->user()->tenant_id;

        $query = StockAdjustment::query()
            ->where('adjustment_type', StockAdjustment::TYPE_PRODUCTION_ENTRY)
            ->whereNotNull('batch_id')
            ->with(['inventoryItem:id,sku,product_name', 'adjustedBy:id,name'])
            ->select('batch_id', 'notes', 'adjusted_by', 'created_at')
            ->selectRaw('SUM(adjustment_quantity) as total_qty')
            ->selectRaw('SUM(adjustment_quantity * unit_cost) as total_cost')
            ->selectRaw('COUNT(*) as item_count')
            ->groupBy('batch_id', 'notes', 'adjusted_by', 'created_at')
            ->latest('created_at');

        if ($request->search) {
            $query->whereHas('inventoryItem', function ($q) use ($request) {
                $q->where('product_name', 'like', "%{$request->search}%")
                    ->orWhere('sku', 'like', "%{$request->search}%");
            });
        }

        $productions = $query->paginate(self::DEFAULT_PER_PAGE)->withQueryString();

        return Inertia::render('SimpleProduction/Index', [
            'productions' => $productions,
            'filters' => $request->only('search'),
        ]);
    }

    public function create(): Response
    {
        $tenantId = auth()->user()->tenant_id;

        $inventoryItems = InventoryItem::query()
            ->select('id', 'sku', 'product_name', 'current_quantity', 'unit_cost')
            ->orderBy('product_name')
            ->get();

        $materials = Material::query()
            ->with(['receipts' => function ($query) {
                $query->where('remaining_quantity', '>', 0)
                    ->whereIn('status', ['active', 'available'])
                    ->select('id', 'material_id', 'receipt_number', 'batch_number', 'remaining_quantity', 'supplier_name', 'price_per_unit', 'unit', 'status')
                    ->orderBy('receipt_date');
            }])
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'unit', 'stock_quantity']);

        $patterns = Pattern::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'output_quantity']);

        $locations = InventoryLocation::active()
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $categories = InventoryItemCategory::active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('SimpleProduction/Create', [
            'inventoryItems' => $inventoryItems,
            'materials' => $materials,
            'patterns' => $patterns,
            'locations' => $locations,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // Finished goods
            'inventory_item_id' => ['nullable', 'integer'],
            'product_name' => ['required_if:inventory_item_id,null', 'nullable', 'string', 'max:255'],
            'product_code' => ['nullable', 'string', 'max:255'],
            'location_id' => ['required', 'integer', 'exists:inventory_locations,id'],
            'category_id' => ['nullable', 'integer', 'exists:inventory_item_categories,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'production_date' => ['required', 'date'],
            'expired_date' => ['nullable', 'date'],
            'pattern_id' => ['nullable', 'integer', 'exists:patterns,id'],

            // Materials consumed
            'materials' => ['required', 'array', 'min:1'],
            'materials.*.material_id' => ['required', 'integer', 'exists:materials,id'],
            'materials.*.quantity' => ['required', 'numeric', 'min:0.001'],
        ]);

        $tenantId = auth()->user()->tenant_id;
        $batchId = Str::uuid()->toString();
        $userId = auth()->id();

        // Check material stock availability first
        $insufficient = $this->stockService->checkStockAvailability($validated['materials']);

        if (! empty($insufficient)) {
            return back()->withErrors([
                'materials' => 'Stok bahan baku tidak mencukupi untuk beberapa item: '.
                    implode(', ', array_column($insufficient, 'material_name')),
            ])->withInput();
        }

        DB::transaction(function () use ($validated, $tenantId, $batchId, $userId) {
            // 1. Get or create finished goods item
            if (empty($validated['inventory_item_id'])) {
                $item = InventoryItem::create([
                    'tenant_id' => $tenantId,
                    'product_name' => $validated['product_name'],
                    'product_code' => $validated['product_code'] ?? 'FG-'.strtoupper(Str::random(5)),
                    'source_type' => 'production',
                    'location_id' => $validated['location_id'],
                    'category_id' => $validated['category_id'] ?? null,
                    'target_quantity' => 0,
                    'current_quantity' => 0,
                    'reserved_quantity' => 0,
                    'minimum_stock' => 5,
                    'unit_cost' => 0,
                    'selling_price' => 0,
                    'status' => 'available',
                    'expired_date' => $validated['expired_date'] ?? null,
                ]);
            } else {
                $item = InventoryItem::findOrFail($validated['inventory_item_id']);
            }

            // 2. Find or create generic Pattern if not provided
            $patternId = $validated['pattern_id'] ?? null;
            if (! $patternId) {
                $genericPattern = Pattern::firstOrCreate(
                    ['tenant_id' => $tenantId, 'code' => 'GENERIC'],
                    [
                        'name' => 'Produksi Sederhana',
                        'output_quantity' => 1,
                        'description' => 'Resep/Pola default untuk produksi sederhana',
                    ]
                );
                $patternId = $genericPattern->id;
            }

            // 3. Create completed PreparationOrder to deduct materials via FIFO automatically
            $prepOrder = PreparationOrder::create([
                'tenant_id' => $tenantId,
                'pattern_id' => $patternId,
                'output_quantity' => $validated['quantity'],
                'output_unit' => 'pcs',
                'material_usage' => array_map(function ($m) {
                    $mat = Material::find($m['material_id']);

                    return [
                        'material_id' => $m['material_id'],
                        'material_name' => $mat?->name ?? 'Unknown',
                        'quantity' => $m['quantity'],
                        'unit' => $mat?->unit ?? 'pcs',
                    ];
                }, $validated['materials']),
                'waste_percentage' => 0,
                'status' => 'completed',
                'preparation_date' => $validated['production_date'],
                'completed_date' => $validated['production_date'],
                'notes' => 'Otomatis dibuat oleh Catatan Produksi Sederhana. '.($validated['notes'] ?? ''),
            ]);

            // 4. Calculate actual cost of goods sold based on deducted material receipts
            $prepOrder->refresh()->load('materialUsages.materialReceipt');
            $totalMaterialCost = 0;
            foreach ($prepOrder->materialUsages as $usage) {
                $totalMaterialCost += $usage->quantity * ($usage->materialReceipt->price_per_unit ?? 0);
            }
            $calculatedUnitCost = $validated['quantity'] > 0 ? $totalMaterialCost / $validated['quantity'] : 0;

            $quantityBefore = $item->current_quantity;
            $quantityAfter = $quantityBefore + $validated['quantity'];

            // 5. Create StockAdjustment for finished goods entry
            StockAdjustment::create([
                'tenant_id' => $tenantId,
                'inventory_item_id' => $item->id,
                'adjustment_type' => StockAdjustment::TYPE_PRODUCTION_ENTRY,
                'batch_id' => $batchId,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $quantityAfter,
                'adjustment_quantity' => $validated['quantity'],
                'reason' => 'Catatan produksi rumahan',
                'notes' => $validated['notes'] ?? null,
                'unit_cost' => $calculatedUnitCost ?: $item->unit_cost ?: 0,
                'adjusted_by' => $userId,
            ]);

            // 6. Update finished goods inventory item stock and unit cost
            $item->increment('current_quantity', $validated['quantity']);

            $updateData = [];
            if ($calculatedUnitCost > 0) {
                $updateData['unit_cost'] = $calculatedUnitCost;
            }
            if (isset($validated['expired_date'])) {
                $updateData['expired_date'] = $validated['expired_date'];
            }
            if (! empty($updateData)) {
                $item->update($updateData);
            }
        });

        return redirect()->route('simple-production.index')
            ->with('success', 'Produksi berhasil dicatat.');
    }

    public function show(string $batchId): Response
    {
        $lines = StockAdjustment::query()
            ->where('batch_id', $batchId)
            ->where('adjustment_type', StockAdjustment::TYPE_PRODUCTION_ENTRY)
            ->with(['inventoryItem:id,sku,product_name', 'adjustedBy:id,name'])
            ->get();

        abort_if($lines->isEmpty(), 404);

        // Find the matching completed PreparationOrder to show the material usage details
        // We can match it based on date and tenant context, or by matching the notes/time
        $date = $lines->first()->created_at->toDateString();
        $prepOrder = PreparationOrder::query()
            ->whereDate('completed_date', $date)
            ->where('status', 'completed')
            ->where('notes', 'like', '%Otomatis dibuat oleh Catatan Produksi Sederhana%')
            ->with(['materialUsages.materialReceipt.material'])
            ->latest('created_at')
            ->first();

        $summary = [
            'batch_id' => $batchId,
            'created_at' => $lines->first()->created_at,
            'adjusted_by' => $lines->first()->adjustedBy,
            'notes' => $lines->first()->notes,
            'total_qty' => $lines->sum('adjustment_quantity'),
            'total_cost' => $lines->sum(fn ($l) => $l->adjustment_quantity * $l->unit_cost),
        ];

        return Inertia::render('SimpleProduction/Show', [
            'summary' => $summary,
            'lines' => $lines,
            'prepOrder' => $prepOrder,
        ]);
    }
}
