<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\StockAdjustment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PurchaseReceiptController extends Controller
{
    public function index(Request $request): Response
    {
        $tenantId = auth()->user()->tenant_id;

        $query = StockAdjustment::query()
            ->where('adjustment_type', StockAdjustment::TYPE_PURCHASE)
            ->whereNotNull('batch_id')
            ->with('inventoryItem:id,sku,product_name')
            ->with('adjustedBy:id,name')
            ->select('batch_id', 'supplier_name', 'purchase_invoice', 'notes', 'adjusted_by', 'created_at')
            ->selectRaw('SUM(adjustment_quantity) as total_qty')
            ->selectRaw('SUM(adjustment_quantity * unit_cost) as total_cost')
            ->selectRaw('COUNT(*) as item_count')
            ->groupBy('batch_id', 'supplier_name', 'purchase_invoice', 'notes', 'adjusted_by', 'created_at')
            ->latest('created_at');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('supplier_name', 'like', "%{$request->search}%")
                    ->orWhere('purchase_invoice', 'like', "%{$request->search}%");
            });
        }

        $receipts = $query->paginate(20)->withQueryString();

        return Inertia::render('Purchase/Index', [
            'receipts' => $receipts,
            'filters' => $request->only('search'),
        ]);
    }

    public function create(): Response
    {
        $items = InventoryItem::query()
            ->select('id', 'sku', 'product_name', 'current_quantity', 'unit_cost')
            ->orderBy('product_name')
            ->get();

        return Inertia::render('Purchase/Create', [
            'inventoryItems' => $items,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_name' => ['required', 'string', 'max:255'],
            'purchase_invoice' => ['nullable', 'string', 'max:255'],
            'purchase_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.inventory_item_id' => ['required', 'integer', 'exists:inventory_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ]);

        $batchId = Str::uuid()->toString();
        $userId = auth()->id();

        DB::transaction(function () use ($validated, $batchId, $userId) {
            foreach ($validated['items'] as $line) {
                $item = InventoryItem::query()->findOrFail($line['inventory_item_id']);

                $quantityBefore = $item->current_quantity;
                $quantityAfter = $quantityBefore + $line['quantity'];

                StockAdjustment::create([
                    'inventory_item_id' => $item->id,
                    'adjustment_type' => StockAdjustment::TYPE_PURCHASE,
                    'batch_id' => $batchId,
                    'quantity_before' => $quantityBefore,
                    'quantity_after' => $quantityAfter,
                    'adjustment_quantity' => $line['quantity'],
                    'reason' => 'Pembelian barang dari supplier',
                    'notes' => $validated['notes'] ?? null,
                    'supplier_name' => $validated['supplier_name'],
                    'purchase_invoice' => $validated['purchase_invoice'] ?? null,
                    'unit_cost' => $line['unit_cost'],
                    'adjusted_by' => $userId,
                ]);

                $item->increment('current_quantity', $line['quantity']);
                $item->update(['unit_cost' => $line['unit_cost']]);
            }
        });

        return redirect()->route('purchase-receipts.index')
            ->with('success', 'Pembelian berhasil dicatat.');
    }

    public function show(string $batchId): Response
    {
        $lines = StockAdjustment::query()
            ->where('batch_id', $batchId)
            ->where('adjustment_type', StockAdjustment::TYPE_PURCHASE)
            ->with('inventoryItem:id,sku,product_name')
            ->with('adjustedBy:id,name')
            ->get();

        abort_if($lines->isEmpty(), 404);

        $summary = [
            'batch_id' => $batchId,
            'supplier_name' => $lines->first()->supplier_name,
            'purchase_invoice' => $lines->first()->purchase_invoice,
            'notes' => $lines->first()->notes,
            'created_at' => $lines->first()->created_at,
            'adjusted_by' => $lines->first()->adjustedBy,
            'total_cost' => $lines->sum(fn ($l) => $l->adjustment_quantity * $l->unit_cost),
            'total_qty' => $lines->sum('adjustment_quantity'),
        ];

        return Inertia::render('Purchase/Show', [
            'summary' => $summary,
            'lines' => $lines,
        ]);
    }
}
