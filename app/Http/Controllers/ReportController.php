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

        $materials = $query->get()->map(function ($material) {
            $totalReceived = $material->receipts->sum('quantity');
            $totalCost = $material->receipts->sum(fn ($r) => $r->quantity * $r->unit_price);

            return [
                'id' => $material->id,
                'code' => $material->code,
                'name' => $material->name,
                'type' => $material->materialType?->name,
                'current_stock' => $material->stock_quantity,
                'unit' => $material->unit,
                'total_received' => $totalReceived,
                'total_cost' => $totalCost,
                'average_price' => $totalReceived > 0 ? $totalCost / $totalReceived : 0,
                'receipts_count' => $material->receipts->count(),
            ];
        });

        return Inertia::render('Reports/MaterialReport', [
            'materials' => $materials,
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
            ->with(['customer:id,name,type', 'items.inventoryItem:id,sku,product_name']);

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

        if ($request->filled('customer_type')) {
            $query->whereHas('customer', function ($q) use ($request) {
                $q->where('type', $request->customer_type);
            });
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
                'customer_type' => $order->customer->type,
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
        $revenueByType = $orders->groupBy('customer_type')
            ->map(fn ($group) => [
                'count' => $group->count(),
                'total' => $group->sum('total_amount'),
            ]);

        return Inertia::render('Reports/SalesReport', [
            'orders' => $orders,
            'summary' => $summary,
            'revenueByType' => $revenueByType,
            'filters' => $request->only(['status', 'customer_type', 'search', 'start_date', 'end_date']),
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
            ->with(['preparationOrder.pattern:id,name,category', 'contractor:id,name,type']);

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
            $efficiency = $order->quantity_requested > 0
                ? ($order->quantity_good / $order->quantity_requested) * 100
                : 0;

            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'pattern_name' => $pattern?->name ?? '-',
                'category' => $pattern?->category ?? '-',
                'type' => $order->type,
                'contractor_name' => $order->contractor?->name ?? 'Internal',
                'quantity_target' => $order->quantity_requested,
                'quantity_good' => $order->quantity_good,
                'quantity_defect' => 0, // Production order doesn't track defects separately
                'quantity_reject' => $order->quantity_reject,
                'efficiency_percentage' => round($efficiency, 2),
                'production_cost' => $order->labor_cost ?? 0,
                'status' => $order->status,
                'start_date' => $order->requested_date?->format('Y-m-d'),
                'target_date' => $order->promised_date?->format('Y-m-d'),
                'completion_date' => $order->completed_date?->format('Y-m-d'),
            ];
        });

        // Summary stats
        $summary = [
            'total_orders' => $orders->count(),
            'total_target' => $orders->sum('quantity_target'),
            'total_produced' => $orders->sum('quantity_good'),
            'total_defect' => $orders->sum('quantity_defect'),
            'total_reject' => $orders->sum('quantity_reject'),
            'average_efficiency' => $orders->avg('efficiency_percentage') ?? 0,
            'total_cost' => $orders->sum('production_cost') ?? 0,
            'completed_orders' => $orders->where('status', 'completed')->count(),
        ];

        return Inertia::render('Reports/ProductionReport', [
            'orders' => $orders,
            'summary' => $summary,
            'filters' => $request->only(['status', 'production_type', 'start_date', 'end_date']),
        ]);
    }
}
