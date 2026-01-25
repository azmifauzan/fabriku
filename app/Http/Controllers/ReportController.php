<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\Material;
use App\Models\ProductionOrder;
use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    /**
     * Material usage report
     */
    public function material(Request $request): Response
    {
        $query = Material::query()
            ->with(['materialType', 'receipts' => function ($query) use ($request) {
                if ($request->filled('start_date')) {
                    $query->where('receipt_date', '>=', $request->start_date);
                }
                if ($request->filled('end_date')) {
                    $query->where('receipt_date', '<=', $request->end_date);
                }
            }]);

        if ($request->filled('material_type')) {
            $query->where('material_type_id', $request->material_type);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'ilike', "%{$request->search}%")
                    ->orWhere('code', 'ilike', "%{$request->search}%");
            });
        }

        // Get completed preparation orders within date range to calculate usage
        $prepOrderQuery = \App\Models\PreparationOrder::query()
            ->where('status', 'completed');

        if ($request->filled('start_date')) {
            $prepOrderQuery->where('completed_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $prepOrderQuery->where('completed_date', '<=', $request->end_date);
        }

        // Calculate total usage per material from completed preparation orders
        $materialUsageMap = [];
        $prepOrders = $prepOrderQuery->get();

        foreach ($prepOrders as $order) {
            $materialsUsed = $order->material_usage ?? [];
            if (is_array($materialsUsed)) {
                foreach ($materialsUsed as $usage) {
                    $materialId = $usage['material_id'] ?? null;
                    $quantity = $usage['quantity'] ?? 0;
                    if ($materialId) {
                        $materialUsageMap[$materialId] = ($materialUsageMap[$materialId] ?? 0) + $quantity;
                    }
                }
            }
        }

        $materials = $query->get()->map(function ($material) use ($materialUsageMap) {
            $totalReceived = $material->receipts->sum('quantity');
            $totalCost = $material->receipts->sum(fn ($r) => $r->quantity * $r->unit_price);
            $totalUsed = $materialUsageMap[$material->id] ?? 0;

            return [
                'id' => $material->id,
                'code' => $material->code,
                'name' => $material->name,
                'type' => $material->materialType?->name,
                'current_stock' => $material->stock_quantity,
                'min_stock' => $material->min_stock,
                'unit' => $material->unit,
                'price_per_unit' => $material->price_per_unit,
                'total_received' => $totalReceived,
                'total_used' => $totalUsed,
                'total_cost' => $totalCost,
                'average_price' => $totalReceived > 0 ? $totalCost / $totalReceived : 0,
                'receipts_count' => $material->receipts->count(),
                'is_low_stock' => $material->stock_quantity <= $material->min_stock,
            ];
        });

        // Summary stats
        $summary = [
            'total_items' => $materials->count(),
            'total_stock_value' => $materials->sum(fn ($m) => $m['current_stock'] * $m['price_per_unit']),
            'total_received' => $materials->sum('total_received'),
            'total_used' => $materials->sum('total_used'),
            'low_stock_count' => $materials->where('is_low_stock', true)->count(),
        ];

        return Inertia::render('Reports/MaterialReport', [
            'materials' => $materials,
            'summary' => $summary,
            'filters' => $request->only(['material_type', 'search', 'start_date', 'end_date']),
        ]);
    }

    /**
     * Inventory report
     */
    public function inventory(Request $request): Response
    {
        $query = InventoryItem::query()
            ->with([
                'location:id,name',
                'tenant:id,business_category',
                'productionOrder.preparationOrder.pattern:id,name',
            ]);

        if ($request->filled('status')) {
            match ($request->status) {
                'available' => $query
                    ->where('current_quantity', '>', 0)
                    ->whereColumn('current_quantity', '>', 'minimum_stock'),
                'low_stock' => $query
                    ->where('current_quantity', '>', 0)
                    ->whereColumn('current_quantity', '<=', 'minimum_stock'),
                'out_of_stock' => $query->where('current_quantity', '=', 0),
                default => null,
            };
        }

        if ($request->filled('category')) {
            $query->whereHas('tenant', function ($tenantQuery) use ($request) {
                $tenantQuery->where('business_category', $request->category);
            });
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('product_name', 'ilike', "%{$request->search}%")
                    ->orWhere('sku', 'ilike', "%{$request->search}%");
            });
        }

        $items = $query->get()->map(function ($item) {
            $quantity = (int) $item->current_quantity;
            $minimumStock = (int) $item->minimum_stock;
            $availableQuantity = max(0, $quantity - (int) $item->reserved_quantity);

            $computedStatus = match (true) {
                $quantity === 0 => 'out_of_stock',
                $quantity <= $minimumStock => 'low_stock',
                default => 'available',
            };

            return [
                'id' => $item->id,
                'sku' => $item->sku,
                'name' => $item->name,
                'category' => $item->tenant?->business_category ?? 'other',
                'quantity' => $quantity,
                'reserved_quantity' => $item->reserved_quantity,
                'available_quantity' => $availableQuantity,
                'minimum_stock' => $minimumStock,
                'unit_price' => (float) $item->unit_cost,
                'total_value' => $quantity * (float) $item->unit_cost,
                'status' => $computedStatus,
                'location' => $item->location?->name,
                'pattern' => $item->productionOrder?->preparationOrder?->pattern?->name,
                'is_low_stock' => $quantity > 0 && $quantity <= $minimumStock,
                'production_date' => $item->production_date?->format('Y-m-d'),
                'expired_date' => $item->expired_date?->format('Y-m-d'),
            ];
        });

        // Summary stats
        $summary = [
            'total_items' => $items->count(),
            'total_quantity' => $items->sum('quantity'),
            'total_value' => $items->sum('total_value'),
            'low_stock_items' => $items->where('is_low_stock', true)->count(),
            'out_of_stock_items' => $items->where('quantity', 0)->count(),
        ];

        return Inertia::render('Reports/InventoryReport', [
            'items' => $items,
            'summary' => $summary,
            'filters' => $request->only(['status', 'category', 'search']),
        ]);
    }

    /**
     * Sales report
     */
    public function sales(Request $request): Response
    {
        $query = SalesOrder::query()
            ->with(['customer:id,name', 'items.inventoryItem:id,sku,product_name']);

        // Date filter
        $startDate = $request->filled('start_date')
            ? $request->start_date
            : now()->startOfMonth()->format('Y-m-d');

        $endDate = $request->filled('end_date')
            ? $request->end_date
            : now()->endOfMonth()->format('Y-m-d');

        $query->whereBetween('order_date', [$startDate, $endDate]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }



        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('order_number', 'ilike', "%{$request->search}%")
                    ->orWhereHas('customer', function ($q) use ($request) {
                        $q->where('name', 'ilike', "%{$request->search}%");
                    });
            });
        }

        $orders = $query->latest('order_date')->get()->map(function ($order) {
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'order_date' => $order->order_date->format('Y-m-d'),
                'customer_name' => $order->customer->name,
                // 'customer_type' => $order->customer->type,
                'total_items' => $order->items->sum('quantity'),
                'subtotal' => $order->subtotal,
                'discount' => $order->discount,
                'total_amount' => $order->total_amount,
                'payment_status' => $order->payment_status,
                'status' => $order->status,
                'items' => $order->items->map(fn ($item) => [
                    'sku' => $item->inventoryItem->sku,
                    'name' => $item->inventoryItem->name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $item->subtotal,
                ]),
            ];
        });

        // Summary stats
        $summary = [
            'total_orders' => $orders->count(),
            'total_revenue' => $orders->sum('total_amount'),
            'total_discount' => $orders->sum('discount'),
            'total_items_sold' => $orders->sum('total_items'),
            'completed_orders' => $orders->where('status', 'completed')->count(),
            'pending_orders' => $orders->where('status', 'pending')->count(),
        ];

        // Revenue by customer type
        // $revenueByType = $orders->groupBy('customer_type')
        //     ->map(fn ($group) => [
        //         'count' => $group->count(),
        //         'total' => $group->sum('total_amount'),
        //     ]);

        return Inertia::render('Reports/SalesReport', [
            'orders' => $orders,
            'summary' => $summary,
            'summary' => $summary,
            // 'revenueByType' => $revenueByType,
            'filters' => $request->only(['status', 'search', 'start_date', 'end_date']),
            'defaultDates' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }

    /**
     * Production report
     */
    public function production(Request $request): Response
    {
        $query = ProductionOrder::query()
            ->with(['preparationOrder.pattern:id,name', 'contractor:id,name,type']);

        // Date filter
        if ($request->filled('start_date')) {
            $query->where('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('created_at', '<=', $request->end_date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('production_type')) {
            $query->where('type', $request->production_type);
        }

        $orders = $query->latest()->get()->map(function ($order) {
            $pattern = $order->preparationOrder?->pattern;
            $outputQuantity = $order->preparationOrder?->output_quantity ?? 0;

            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'pattern_name' => $pattern?->name ?? '-',
                'category' => $pattern?->category ?? '-',
                'type' => $order->type,
                'contractor_name' => $order->contractor?->name ?? 'Internal',
                'output_quantity' => $outputQuantity,
                'production_cost' => $order->labor_cost ?? 0,
                'status' => $order->status,
                'sent_date' => $order->sent_date?->format('Y-m-d'),
                'estimated_date' => $order->estimated_completion_date?->format('Y-m-d'),
                'completion_date' => $order->completed_date?->format('Y-m-d'),
            ];
        });

        // Summary stats
        $summary = [
            'total_orders' => $orders->count(),
            'total_output' => $orders->sum('output_quantity'),
            'total_cost' => $orders->sum('production_cost') ?? 0,
            'completed_orders' => $orders->where('status', 'completed')->count(),
            'in_progress_orders' => $orders->where('status', 'in_progress')->count(),
        ];

        return Inertia::render('Reports/ProductionReport', [
            'orders' => $orders,
            'summary' => $summary,
            'filters' => $request->only(['status', 'production_type', 'start_date', 'end_date']),
        ]);
    }
}
