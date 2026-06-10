<?php

namespace App\Http\Controllers;

use App\Exports\InventoryReportExport;
use App\Exports\MaterialReportExport;
use App\Exports\ProductionReportExport;
use App\Exports\PurchaseReportExport;
use App\Exports\SalesRecapExport;
use App\Exports\SalesReportExport;
use App\Exports\ServiceReportExport;
use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\Material;
use App\Models\PreparationOrder;
use App\Models\ProductionOrder;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\StockAdjustment;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    /**
     * Material usage report
     */
    public function material(Request $request): InertiaResponse
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
        $prepOrderQuery = PreparationOrder::query()
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
            $totalCost = $material->receipts->sum(fn ($r) => $r->quantity * $r->price_per_unit);
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
    public function inventory(Request $request): InertiaResponse
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
    public function sales(Request $request): InertiaResponse
    {
        $query = SalesOrder::query()
            ->with(['customer:id,name', 'items.inventoryItem:id,sku,product_name', 'items.service:id,code,name']);

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

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
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
                'customer_name' => $order->customer?->name ?? '-',
                'total_items' => $order->items->sum('quantity'),
                'subtotal' => $order->subtotal,
                'discount' => $order->discount,
                'total_amount' => $order->total_amount,
                'payment_status' => $order->payment_status,
                'status' => $order->status,
                'items' => $order->items->map(fn ($item) => [
                    'sku' => $item->inventoryItem?->sku ?? $item->service?->code ?? '-',
                    'name' => $item->inventoryItem?->name ?? $item->service?->name ?? '-',
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

        // Per-customer stats from the filtered orders
        $customerStats = $orders
            ->groupBy('customer_name')
            ->map(fn ($group, $name) => [
                'customer_name' => $name,
                'transaction_count' => $group->count(),
                'total_items' => $group->sum('total_items'),
                'total_amount' => $group->sum('total_amount'),
            ])
            ->values()
            ->sortByDesc('transaction_count')
            ->values();

        $customers = Customer::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Reports/SalesReport', [
            'orders' => $orders,
            'summary' => $summary,
            'customerStats' => $customerStats,
            'customers' => $customers,
            'filters' => $request->only(['status', 'search', 'start_date', 'end_date', 'customer_id']),
            'defaultDates' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }

    /**
     * Production report
     */
    public function production(Request $request): InertiaResponse
    {
        $query = ProductionOrder::query()
            ->with(['preparationOrder.pattern:id,name', 'contractor:id,name,type', 'preparationOrder.materialUsages.materialReceipt']);

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
                'labor_cost' => $order->labor_cost ?? 0,
                'material_cost' => $order->preparationOrder?->materialUsages->sum(fn ($u) => $u->quantity * ($u->materialReceipt->price_per_unit ?? 0)) ?? 0,
                'total_cost' => ($order->labor_cost ?? 0) + ($order->preparationOrder?->materialUsages->sum(fn ($u) => $u->quantity * ($u->materialReceipt->price_per_unit ?? 0)) ?? 0),
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
            'total_labor_cost' => $orders->sum('labor_cost'),
            'total_material_cost' => $orders->sum('material_cost'),
            'total_cost' => $orders->sum('total_cost'),
            'completed_orders' => $orders->where('status', 'completed')->count(),
            'in_progress_orders' => $orders->where('status', 'in_progress')->count(),
        ];

        return Inertia::render('Reports/ProductionReport', [
            'orders' => $orders,
            'summary' => $summary,
            'filters' => $request->only(['status', 'production_type', 'start_date', 'end_date']),
        ]);
    }

    /**
     * Sales Recapitulation per Customer
     */
    public function salesRecap(Request $request): InertiaResponse
    {
        $query = SalesOrder::query()
            ->select('customer_id', DB::raw('count(*) as total_orders'), DB::raw('sum(total_amount) as total_revenue'), DB::raw('sum(paid_amount) as total_paid'))
            ->with('customer:id,name')
            ->groupBy('customer_id');

        // Date filter
        $startDate = $request->filled('start_date')
            ? $request->start_date
            : now()->startOfMonth()->format('Y-m-d');

        $endDate = $request->filled('end_date')
            ? $request->end_date
            : now()->endOfMonth()->format('Y-m-d');

        // We filter by order_date
        $query->whereBetween('order_date', [$startDate, $endDate]);

        if ($request->filled('search')) {
            $query->whereHas('customer', function ($q) use ($request) {
                $q->where('name', 'ilike', "%{$request->search}%");
            });
        }

        $recap = $query->get()->map(function ($item) {
            return [
                'customer_id' => $item->customer_id,
                'customer_name' => $item->customer->name,
                'total_orders' => $item->total_orders,
                'total_revenue' => $item->total_revenue,
                'total_paid' => $item->total_paid,
                'outstanding' => $item->total_revenue - $item->total_paid,
            ];
        });

        return Inertia::render('Reports/SalesRecap', [
            'recap' => $recap,
            'summary' => [
                'total_customers' => $recap->count(),
                'total_revenue' => $recap->sum('total_revenue'),
                'total_paid' => $recap->sum('total_paid'),
                'total_outstanding' => $recap->sum('outstanding'),
            ],
            'filters' => $request->only(['search', 'start_date', 'end_date']),
            'defaultDates' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }

    /**
     * Export Material Report
     */
    public function exportMaterial(Request $request): Response|BinaryFileResponse
    {
        $data = $this->getMaterialReportData($request);

        if ($request->format === 'pdf') {
            $pdf = Pdf::loadView('pdf.material-report', $data)
                ->setPaper('a4', 'landscape');

            return $pdf->download('laporan-material-'.now()->format('Y-m-d').'.pdf');
        }

        return Excel::download(
            new MaterialReportExport(
                collect($data['materials']),
                $data['summary'],
                $data['filters']
            ),
            'laporan-material-'.now()->format('Y-m-d').'.xlsx'
        );
    }

    /**
     * Export Inventory Report
     */
    public function exportInventory(Request $request): Response|BinaryFileResponse
    {
        $data = $this->getInventoryReportData($request);

        if ($request->format === 'pdf') {
            $pdf = Pdf::loadView('pdf.inventory-report', $data)
                ->setPaper('a4', 'landscape');

            return $pdf->download('laporan-inventory-'.now()->format('Y-m-d').'.pdf');
        }

        return Excel::download(
            new InventoryReportExport(
                collect($data['items']),
                $data['summary'],
                $data['filters']
            ),
            'laporan-inventory-'.now()->format('Y-m-d').'.xlsx'
        );
    }

    /**
     * Export Sales Report
     */
    public function exportSales(Request $request): Response|BinaryFileResponse
    {
        $data = $this->getSalesReportData($request);

        if ($request->format === 'pdf') {
            $pdf = Pdf::loadView('pdf.sales-report', $data)
                ->setPaper('a4', 'landscape');

            return $pdf->download('laporan-penjualan-'.now()->format('Y-m-d').'.pdf');
        }

        return Excel::download(
            new SalesReportExport(
                collect($data['orders']),
                $data['summary'],
                $data['filters']
            ),
            'laporan-penjualan-'.now()->format('Y-m-d').'.xlsx'
        );
    }

    /**
     * Export Sales Recap
     */
    public function exportSalesRecap(Request $request): Response|BinaryFileResponse
    {
        $data = $this->getSalesRecapData($request);

        if ($request->format === 'pdf') {
            $pdf = Pdf::loadView('pdf.sales-recap', $data)
                ->setPaper('a4', 'portrait');

            return $pdf->download('rekapitulasi-penjualan-'.now()->format('Y-m-d').'.pdf');
        }

        return Excel::download(
            new SalesRecapExport(
                collect($data['recap']),
                $data['summary'],
                $data['filters']
            ),
            'rekapitulasi-penjualan-'.now()->format('Y-m-d').'.xlsx'
        );
    }

    /**
     * Export Production Report
     */
    public function exportProduction(Request $request): Response|BinaryFileResponse
    {
        $data = $this->getProductionReportData($request);

        if ($request->format === 'pdf') {
            $pdf = Pdf::loadView('pdf.production-report', $data)
                ->setPaper('a4', 'landscape');

            return $pdf->download('laporan-produksi-'.now()->format('Y-m-d').'.pdf');
        }

        return Excel::download(
            new ProductionReportExport(
                collect($data['orders']),
                $data['summary'],
                $data['filters']
            ),
            'laporan-produksi-'.now()->format('Y-m-d').'.xlsx'
        );
    }

    /**
     * Get Material Report Data
     */
    private function getMaterialReportData(Request $request): array
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

        $prepOrderQuery = PreparationOrder::query()
            ->where('status', 'completed');

        if ($request->filled('start_date')) {
            $prepOrderQuery->where('completed_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $prepOrderQuery->where('completed_date', '<=', $request->end_date);
        }

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
            $totalCost = $material->receipts->sum(fn ($r) => $r->quantity * $r->price_per_unit);
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

        $summary = [
            'total_items' => $materials->count(),
            'total_stock_value' => $materials->sum(fn ($m) => $m['current_stock'] * $m['price_per_unit']),
            'total_received' => $materials->sum('total_received'),
            'total_used' => $materials->sum('total_used'),
            'low_stock_count' => $materials->where('is_low_stock', true)->count(),
        ];

        return [
            'materials' => $materials,
            'summary' => $summary,
            'filters' => $request->only(['material_type', 'search', 'start_date', 'end_date']),
        ];
    }

    /**
     * Get Inventory Report Data
     */
    private function getInventoryReportData(Request $request): array
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

        $summary = [
            'total_items' => $items->count(),
            'total_quantity' => $items->sum('quantity'),
            'total_value' => $items->sum('total_value'),
            'low_stock_items' => $items->where('is_low_stock', true)->count(),
            'out_of_stock_items' => $items->where('quantity', 0)->count(),
        ];

        return [
            'items' => $items,
            'summary' => $summary,
            'filters' => $request->only(['status', 'category', 'search']),
        ];
    }

    /**
     * Get Sales Report Data
     */
    private function getSalesReportData(Request $request): array
    {
        $query = SalesOrder::query()
            ->with(['customer:id,name', 'items.inventoryItem:id,sku,product_name', 'items.service:id,code,name']);

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
                'total_items' => $order->items->sum('quantity'),
                'subtotal' => $order->subtotal,
                'discount' => $order->discount,
                'total_amount' => $order->total_amount,
                'payment_status' => $order->payment_status,
                'status' => $order->status,
            ];
        });

        $summary = [
            'total_orders' => $orders->count(),
            'total_revenue' => $orders->sum('total_amount'),
            'total_discount' => $orders->sum('discount'),
            'total_items_sold' => $orders->sum('total_items'),
            'completed_orders' => $orders->where('status', 'completed')->count(),
            'pending_orders' => $orders->where('status', 'pending')->count(),
        ];

        return [
            'orders' => $orders,
            'summary' => $summary,
            'filters' => array_merge(
                $request->only(['status', 'search']),
                ['start_date' => $startDate, 'end_date' => $endDate]
            ),
        ];
    }

    /**
     * Get Sales Recap Data
     */
    private function getSalesRecapData(Request $request): array
    {
        $query = SalesOrder::query()
            ->select('customer_id', DB::raw('count(*) as total_orders'), DB::raw('sum(total_amount) as total_revenue'), DB::raw('sum(paid_amount) as total_paid'))
            ->with('customer:id,name')
            ->groupBy('customer_id');

        $startDate = $request->filled('start_date')
            ? $request->start_date
            : now()->startOfMonth()->format('Y-m-d');

        $endDate = $request->filled('end_date')
            ? $request->end_date
            : now()->endOfMonth()->format('Y-m-d');

        $query->whereBetween('order_date', [$startDate, $endDate]);

        if ($request->filled('search')) {
            $query->whereHas('customer', function ($q) use ($request) {
                $q->where('name', 'ilike', "%{$request->search}%");
            });
        }

        $recap = $query->get()->map(function ($item) {
            return [
                'customer_id' => $item->customer_id,
                'customer_name' => $item->customer->name,
                'total_orders' => $item->total_orders,
                'total_revenue' => $item->total_revenue,
                'total_paid' => $item->total_paid,
                'outstanding' => $item->total_revenue - $item->total_paid,
            ];
        });

        $summary = [
            'total_customers' => $recap->count(),
            'total_revenue' => $recap->sum('total_revenue'),
            'total_paid' => $recap->sum('total_paid'),
            'total_outstanding' => $recap->sum('outstanding'),
        ];

        return [
            'recap' => $recap,
            'summary' => $summary,
            'filters' => array_merge(
                $request->only(['search']),
                ['start_date' => $startDate, 'end_date' => $endDate]
            ),
        ];
    }

    /**
     * Get Production Report Data
     */
    private function getProductionReportData(Request $request): array
    {
        $query = ProductionOrder::query()
            ->with(['preparationOrder.pattern:id,name', 'contractor:id,name,type', 'preparationOrder.materialUsages.materialReceipt']);

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
                'labor_cost' => $order->labor_cost ?? 0,
                'material_cost' => $order->preparationOrder?->materialUsages->sum(fn ($u) => $u->quantity * ($u->materialReceipt->price_per_unit ?? 0)) ?? 0,
                'total_cost' => ($order->labor_cost ?? 0) + ($order->preparationOrder?->materialUsages->sum(fn ($u) => $u->quantity * ($u->materialReceipt->price_per_unit ?? 0)) ?? 0),
                'status' => $order->status,
                'sent_date' => $order->sent_date?->format('Y-m-d'),
                'estimated_date' => $order->estimated_completion_date?->format('Y-m-d'),
                'completion_date' => $order->completed_date?->format('Y-m-d'),
            ];
        });

        $summary = [
            'total_orders' => $orders->count(),
            'total_output' => $orders->sum('output_quantity'),
            'total_labor_cost' => $orders->sum('labor_cost'),
            'total_material_cost' => $orders->sum('material_cost'),
            'total_cost' => $orders->sum('total_cost'),
            'completed_orders' => $orders->where('status', 'completed')->count(),
            'in_progress_orders' => $orders->where('status', 'in_progress')->count(),
        ];

        return [
            'orders' => $orders,
            'summary' => $summary,
            'filters' => $request->only(['status', 'production_type', 'start_date', 'end_date']),
        ];
    }

    /**
     * Purchase report
     */
    public function purchase(Request $request): InertiaResponse
    {
        $data = $this->getPurchaseReportData($request);

        return Inertia::render('Reports/PurchaseReport', [
            'batches' => $data['batches'],
            'summary' => $data['summary'],
            'filters' => $data['filters'],
            'defaultDates' => [
                'start_date' => $data['filters']['start_date'],
                'end_date' => $data['filters']['end_date'],
            ],
        ]);
    }

    /**
     * Export Purchase Report
     */
    public function exportPurchase(Request $request): Response|BinaryFileResponse
    {
        $data = $this->getPurchaseReportData($request);

        if ($request->format === 'pdf') {
            $pdf = Pdf::loadView('pdf.purchase-report', $data)
                ->setPaper('a4', 'landscape');

            return $pdf->download('laporan-pembelian-'.now()->format('Y-m-d').'.pdf');
        }

        return Excel::download(
            new PurchaseReportExport(
                collect($data['batches']),
                $data['summary'],
                $data['filters']
            ),
            'laporan-pembelian-'.now()->format('Y-m-d').'.xlsx'
        );
    }

    /**
     * Service report (kategori jasa)
     */
    public function service(Request $request): InertiaResponse
    {
        $data = $this->getServiceReportData($request);

        return Inertia::render('Reports/ServiceReport', [
            'services' => $data['services'],
            'staffRecap' => $data['staffRecap'],
            'summary' => $data['summary'],
            'filters' => $data['filters'],
            'defaultDates' => [
                'start_date' => $data['filters']['start_date'],
                'end_date' => $data['filters']['end_date'],
            ],
        ]);
    }

    /**
     * Export Service Report
     */
    public function exportService(Request $request): Response|BinaryFileResponse
    {
        $data = $this->getServiceReportData($request);

        if ($request->format === 'pdf') {
            $pdf = Pdf::loadView('pdf.service-report', $data)
                ->setPaper('a4', 'landscape');

            return $pdf->download('laporan-layanan-'.now()->format('Y-m-d').'.pdf');
        }

        return Excel::download(
            new ServiceReportExport(
                collect($data['services']),
                collect($data['staffRecap']),
                $data['summary'],
                $data['filters']
            ),
            'laporan-layanan-'.now()->format('Y-m-d').'.xlsx'
        );
    }

    /**
     * Get Service Report Data
     */
    private function getServiceReportData(Request $request): array
    {
        $startDate = $request->filled('start_date')
            ? $request->start_date
            : now()->startOfMonth()->format('Y-m-d');

        $endDate = $request->filled('end_date')
            ? $request->end_date
            : now()->endOfMonth()->format('Y-m-d');

        $lines = SalesOrderItem::query()
            ->whereNotNull('service_id')
            ->whereHas('salesOrder', function ($q) use ($startDate, $endDate) {
                $q->where('tenant_id', auth()->user()->tenant_id)
                    ->where('status', '!=', 'cancelled')
                    ->whereBetween('order_date', [$startDate, $endDate]);
            })
            ->with(['service:id,code,name,category', 'servedBy:id,name'])
            ->get();

        $services = $lines->groupBy('service_id')->map(function ($group) {
            $service = $group->first()->service;

            return [
                'code' => $service?->code ?? '-',
                'name' => $service?->name ?? '-',
                'category' => $service?->category,
                'total_sold' => $group->sum('quantity'),
                'total_revenue' => (float) $group->sum('subtotal'),
            ];
        })->sortByDesc('total_revenue')->values();

        $staffRecap = $lines->whereNotNull('served_by')->groupBy('served_by')->map(function ($group) {
            return [
                'staff_name' => $group->first()->servedBy?->name ?? '-',
                'total_services' => $group->sum('quantity'),
                'total_revenue' => (float) $group->sum('subtotal'),
            ];
        })->sortByDesc('total_revenue')->values();

        $summary = [
            'total_service_types' => $services->count(),
            'total_services_sold' => $lines->sum('quantity'),
            'total_revenue' => (float) $lines->sum('subtotal'),
        ];

        return [
            'services' => $services,
            'staffRecap' => $staffRecap,
            'summary' => $summary,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ];
    }

    /**
     * Get Purchase Report Data
     */
    private function getPurchaseReportData(Request $request): array
    {
        $query = StockAdjustment::query()
            ->where('adjustment_type', StockAdjustment::TYPE_PURCHASE)
            ->whereNotNull('batch_id')
            ->with(['inventoryItem:id,sku,product_name', 'adjustedBy:id,name']);

        // Date filter
        $startDate = $request->filled('start_date')
            ? $request->start_date
            : now()->startOfMonth()->format('Y-m-d');

        $endDate = $request->filled('end_date')
            ? $request->end_date
            : now()->endOfMonth()->format('Y-m-d');

        $query->whereBetween('created_at', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay(),
        ]);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('supplier_name', 'ilike', "%{$request->search}%")
                    ->orWhere('purchase_invoice', 'ilike', "%{$request->search}%")
                    ->orWhereHas('inventoryItem', function ($q2) use ($request) {
                        $q2->where('product_name', 'ilike', "%{$request->search}%")
                            ->orWhere('sku', 'ilike', "%{$request->search}%");
                    });
            });
        }

        $adjustments = $query->latest('created_at')->get();

        // Group by batch_id
        $batches = $adjustments->groupBy('batch_id')->map(function ($group) {
            $first = $group->first();

            return [
                'batch_id' => $first->batch_id,
                'date' => $first->created_at->format('Y-m-d'),
                'supplier_name' => $first->supplier_name,
                'purchase_invoice' => $first->purchase_invoice,
                'notes' => $first->notes,
                'adjusted_by' => $first->adjustedBy?->name ?? 'System',
                'item_count' => $group->count(),
                'total_qty' => $group->sum('adjustment_quantity'),
                'total_cost' => $group->sum(fn ($item) => $item->adjustment_quantity * $item->unit_cost),
                'items' => $group->map(fn ($item) => [
                    'sku' => $item->inventoryItem?->sku ?? '-',
                    'name' => $item->inventoryItem?->product_name ?? '-',
                    'quantity' => $item->adjustment_quantity,
                    'unit_cost' => $item->unit_cost,
                    'total_cost' => $item->adjustment_quantity * $item->unit_cost,
                ]),
            ];
        })->values();

        $summary = [
            'total_transactions' => $batches->count(),
            'total_items_purchased' => $batches->sum('total_qty'),
            'total_spend' => $batches->sum('total_cost'),
        ];

        return [
            'batches' => $batches,
            'summary' => $summary,
            'filters' => array_merge(
                $request->only(['search']),
                ['start_date' => $startDate, 'end_date' => $endDate]
            ),
        ];
    }
}
