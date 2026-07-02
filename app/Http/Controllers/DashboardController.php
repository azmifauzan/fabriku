<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\Material;
use App\Models\PreparationOrder;
use App\Models\ProductionOrder;
use App\Models\SalesOrder;
use App\Models\Service;
use App\Models\StockAdjustment;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response|RedirectResponse
    {
        $tenantId = auth()->user()->tenant_id;

        /** @var Tenant $tenant */
        $tenant = auth()->user()->tenant;
        $categoryConfig = $tenant?->getCategoryConfig() ?? [];
        $enableProductionFlow = $categoryConfig['rules']['enable_production_flow'] ?? true;
        $enableSimpleProduction = $categoryConfig['rules']['enable_simple_production'] ?? false;

        $enableServiceModule = $categoryConfig['rules']['enable_service_module'] ?? false;

        if (! $enableProductionFlow) {
            if ($enableSimpleProduction) {
                if (request()->query('view') !== 'stats') {
                    return redirect()->route('sales-orders.quick-checkout');
                }

                return $this->homemadeDashboard($tenantId);
            }
            if ($enableServiceModule) {
                if (request()->query('view') !== 'stats') {
                    return redirect()->route('sales-orders.quick-checkout');
                }

                return $this->serviceDashboard($tenantId);
            }
            if (request()->query('view') !== 'stats') {
                return redirect()->route('sales-orders.quick-checkout');
            }

            return $this->retailDashboard($tenantId);
        }

        // KPI Cards
        $stats = [
            'total_materials' => Material::query()->count(),
            'low_stock_materials' => Material::query()
                ->whereColumn('stock_quantity', '<=', 'min_stock')
                ->count(),
            'total_inventory' => InventoryItem::query()->sum('current_quantity'),
            'low_stock_inventory' => InventoryItem::query()
                ->whereColumn('current_quantity', '<=', 'minimum_stock')
                ->count(),
            'total_sales_month' => SalesOrder::query()
                ->whereMonth('order_date', now()->month)
                ->whereYear('order_date', now()->year)
                ->sum('total_amount'),
            'total_sales_count' => SalesOrder::query()
                ->whereMonth('order_date', now()->month)
                ->whereYear('order_date', now()->year)
                ->count(),
            'pending_production' => ProductionOrder::query()
                ->whereIn('status', ['draft', 'pending', 'in_progress'])
                ->count(),
            'pending_preparation' => PreparationOrder::query()
                ->whereIn('status', ['draft', 'in_progress'])
                ->count(),
            'realized_revenue' => SalesOrder::query()
                ->sum('paid_amount'),
            'outstanding_receivables' => SalesOrder::query()
                ->where('payment_status', '!=', 'paid')
                ->where('status', '!=', 'cancelled')
                ->sum(DB::raw('total_amount - paid_amount')),
        ];

        // Sales Trend (last 7 days)
        $salesTrend = SalesOrder::query()
            ->select(
                DB::raw('DATE(order_date) as date'),
                DB::raw('SUM(total_amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->where('order_date', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top Selling Products (last 30 days)
        $topProducts = DB::table('sales_order_items')
            ->join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.id')
            ->join('inventory_items', 'sales_order_items.inventory_item_id', '=', 'inventory_items.id')
            ->select(
                'inventory_items.sku',
                'inventory_items.product_name as name',
                DB::raw('SUM(sales_order_items.quantity) as total_sold'),
                DB::raw('SUM(sales_order_items.subtotal) as total_revenue')
            )
            ->where('sales_orders.tenant_id', $tenantId)
            ->where('sales_orders.order_date', '>=', now()->subDays(30))
            ->groupBy('inventory_items.id', 'inventory_items.sku', 'inventory_items.product_name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        // Recent Activities (latest 10)
        $recentActivities = collect();

        // Recent sales
        $recentSales = SalesOrder::query()
            ->with('customer:id,name')
            ->select('id', 'order_number', 'customer_id', 'total_amount', 'status', 'created_at')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($order) => [
                'type' => 'sale',
                'description' => "Order {$order->order_number} - {$order->customer->name}",
                'amount' => $order->total_amount,
                'status' => $order->status,
                'date' => $order->created_at,
            ]);

        // Recent production
        $recentProduction = ProductionOrder::query()
            ->with('preparationOrder.pattern:id,name')
            ->select('id', 'order_number', 'preparation_order_id', 'status', 'created_at')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($order) => [
                'type' => 'production',
                'description' => "Production {$order->order_number}".
                    ($order->preparationOrder?->pattern ? " - {$order->preparationOrder->pattern->name}" : ''),
                'status' => $order->status,
                'date' => $order->created_at,
            ]);

        $recentActivities = $recentSales->concat($recentProduction)
            ->sortByDesc('date')
            ->take(10)
            ->values();

        // Low Stock Alerts
        $lowStockMaterials = Material::query()
            ->whereColumn('stock_quantity', '<=', 'min_stock')
            ->select('id', 'code', 'name', 'stock_quantity', 'unit', 'min_stock')
            ->limit(5)
            ->get();

        $lowStockInventory = InventoryItem::query()
            ->whereColumn('current_quantity', '<=', 'minimum_stock')
            ->select('id', 'sku', 'product_name', 'current_quantity', 'reserved_quantity', 'minimum_stock')
            ->limit(5)
            ->get();

        // Material Stock Summary — aggregated at DB level
        $materialStockSummary = [
            'total_items' => Material::query()->count(),
            'total_stock_value' => Material::query()->sum(DB::raw('stock_quantity * price_per_unit')),
            'low_stock_count' => Material::query()->whereColumn('stock_quantity', '<=', 'min_stock')->count(),
        ];

        // Top 5 materials by stock value
        $topMaterialsByValue = Material::query()
            ->with('materialType:id,name')
            ->select('id', 'code', 'name', 'stock_quantity', 'unit', 'price_per_unit', 'material_type_id', 'min_stock')
            ->orderByDesc(DB::raw('stock_quantity * price_per_unit'))
            ->limit(5)
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'code' => $m->code,
                'name' => $m->name,
                'type' => $m->materialType?->name,
                'stock_quantity' => $m->stock_quantity,
                'unit' => $m->unit,
                'price_per_unit' => $m->price_per_unit,
                'stock_value' => $m->stock_quantity * $m->price_per_unit,
                'is_low_stock' => $m->stock_quantity <= $m->min_stock,
            ]);

        // Inventory by Location Summary
        $inventoryByLocation = InventoryLocation::query()
            ->active()
            ->withCount('inventoryItems')
            ->withSum('inventoryItems', 'current_quantity')
            ->orderBy('code')
            ->limit(6)
            ->get()
            ->map(function ($location) {
                // If capacity is null (unlimited), use 0 for calculation safety or handle in frontend
                // Actually if capacity is null, it's unlimited.
                $usedCapacity = $location->inventory_items_sum_current_quantity ?? 0;

                return [
                    'id' => $location->id,
                    'name' => $location->name,
                    'code' => $location->code,
                    'type' => $location->type,
                    'item_count' => $location->inventory_items_count,
                    'used_capacity' => $usedCapacity,
                    'capacity' => $location->capacity,
                    // If capacity is null, percentage is 0 (or irrelevant)
                    'percentage' => $location->capacity ? round(($usedCapacity / $location->capacity) * 100) : 0,
                    'is_unlimited' => is_null($location->capacity),
                ];
            });

        // Comprehensive Inventory Summary — aggregated at DB level
        $inventorySummary = [
            'total_items' => InventoryItem::query()->count(),
            'total_quantity' => InventoryItem::query()->sum('current_quantity'),
            'total_reserved' => InventoryItem::query()->sum('reserved_quantity'),
            'total_available' => InventoryItem::query()->sum(DB::raw('CASE WHEN current_quantity > reserved_quantity THEN current_quantity - reserved_quantity ELSE 0 END')),
            'total_value' => InventoryItem::query()->sum(DB::raw('current_quantity * unit_cost')),
            'low_stock_count' => InventoryItem::query()
                ->whereRaw('(current_quantity - reserved_quantity) > 0')
                ->whereRaw('(current_quantity - reserved_quantity) <= minimum_stock')
                ->count(),
            'out_of_stock_count' => InventoryItem::query()->where('current_quantity', 0)->count(),
        ];

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'salesTrend' => $salesTrend,
            'topProducts' => $topProducts,
            'recentActivities' => $recentActivities,
            'lowStockMaterials' => $lowStockMaterials,
            'lowStockInventory' => $lowStockInventory,
            'materialStockSummary' => $materialStockSummary,
            'topMaterialsByValue' => $topMaterialsByValue,
            'inventoryByLocation' => $inventoryByLocation,
            'inventorySummary' => $inventorySummary,
        ]);
    }

    private function retailDashboard(int $tenantId): Response
    {
        $today = now()->toDateString();
        $thisMonth = now()->month;
        $thisYear = now()->year;

        $stats = [
            'sales_today' => SalesOrder::query()
                ->whereDate('order_date', $today)
                ->sum('total_amount'),
            'sales_today_count' => SalesOrder::query()
                ->whereDate('order_date', $today)
                ->count(),
            'sales_month' => SalesOrder::query()
                ->whereMonth('order_date', $thisMonth)
                ->whereYear('order_date', $thisYear)
                ->sum('total_amount'),
            'sales_month_count' => SalesOrder::query()
                ->whereMonth('order_date', $thisMonth)
                ->whereYear('order_date', $thisYear)
                ->count(),
            'total_inventory_items' => InventoryItem::query()->count(),
            'low_stock_count' => InventoryItem::query()
                ->whereRaw('(current_quantity - reserved_quantity) <= minimum_stock')
                ->whereRaw('(current_quantity - reserved_quantity) >= 0')
                ->count(),
            'out_of_stock_count' => InventoryItem::query()->where('current_quantity', 0)->count(),
            'inventory_value' => InventoryItem::query()->sum(DB::raw('current_quantity * unit_cost')),
            'outstanding_receivables' => SalesOrder::query()
                ->where('payment_status', '!=', 'paid')
                ->where('status', '!=', 'cancelled')
                ->sum(DB::raw('total_amount - paid_amount')),
        ];

        $salesTrend = SalesOrder::query()
            ->select(
                DB::raw('DATE(order_date) as date'),
                DB::raw('SUM(total_amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->where('order_date', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $topProducts = DB::table('sales_order_items')
            ->join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.id')
            ->join('inventory_items', 'sales_order_items.inventory_item_id', '=', 'inventory_items.id')
            ->select(
                'inventory_items.sku',
                'inventory_items.product_name as name',
                DB::raw('SUM(sales_order_items.quantity) as total_sold'),
                DB::raw('SUM(sales_order_items.subtotal) as total_revenue')
            )
            ->where('sales_orders.tenant_id', $tenantId)
            ->where('sales_orders.order_date', '>=', now()->subDays(30))
            ->groupBy('inventory_items.id', 'inventory_items.sku', 'inventory_items.product_name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        $lowStockItems = InventoryItem::query()
            ->whereRaw('(current_quantity - reserved_quantity) <= minimum_stock')
            ->select('id', 'sku', 'product_name', 'current_quantity', 'reserved_quantity', 'minimum_stock')
            ->limit(10)
            ->get();

        $recentPurchases = StockAdjustment::query()
            ->where('adjustment_type', StockAdjustment::TYPE_PURCHASE)
            ->whereNotNull('batch_id')
            ->select('batch_id', 'supplier_name', 'created_at')
            ->selectRaw('SUM(adjustment_quantity * unit_cost) as total_cost')
            ->groupBy('batch_id', 'supplier_name', 'created_at')
            ->latest('created_at')
            ->limit(5)
            ->get();

        return Inertia::render('RetailDashboard', [
            'stats' => $stats,
            'salesTrend' => $salesTrend,
            'topProducts' => $topProducts,
            'lowStockItems' => $lowStockItems,
            'recentPurchases' => $recentPurchases,
        ]);
    }

    private function homemadeDashboard(int $tenantId): Response
    {
        $today = now()->toDateString();
        $thisMonth = now()->month;
        $thisYear = now()->year;

        $stats = [
            'sales_today' => SalesOrder::query()
                ->whereDate('order_date', $today)
                ->sum('total_amount'),
            'sales_today_count' => SalesOrder::query()
                ->whereDate('order_date', $today)
                ->count(),
            'sales_month' => SalesOrder::query()
                ->whereMonth('order_date', $thisMonth)
                ->whereYear('order_date', $thisYear)
                ->sum('total_amount'),
            'sales_month_count' => SalesOrder::query()
                ->whereMonth('order_date', $thisMonth)
                ->whereYear('order_date', $thisYear)
                ->count(),
            'total_inventory_items' => InventoryItem::query()->count(),
            'low_stock_count' => InventoryItem::query()
                ->whereRaw('(current_quantity - reserved_quantity) <= minimum_stock')
                ->whereRaw('(current_quantity - reserved_quantity) >= 0')
                ->count(),
            'out_of_stock_count' => InventoryItem::query()->where('current_quantity', 0)->count(),
            'inventory_value' => InventoryItem::query()->sum(DB::raw('current_quantity * unit_cost')),
            'production_today' => (int) StockAdjustment::query()
                ->where('adjustment_type', StockAdjustment::TYPE_PRODUCTION_ENTRY)
                ->whereDate('created_at', $today)
                ->sum('adjustment_quantity'),
            'low_stock_materials_count' => Material::query()
                ->whereColumn('stock_quantity', '<=', 'min_stock')
                ->count(),
        ];

        $salesTrend = SalesOrder::query()
            ->select(
                DB::raw('DATE(order_date) as date'),
                DB::raw('SUM(total_amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->where('order_date', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $topProducts = DB::table('sales_order_items')
            ->join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.id')
            ->join('inventory_items', 'sales_order_items.inventory_item_id', '=', 'inventory_items.id')
            ->select(
                'inventory_items.sku',
                'inventory_items.product_name as name',
                DB::raw('SUM(sales_order_items.quantity) as total_sold'),
                DB::raw('SUM(sales_order_items.subtotal) as total_revenue')
            )
            ->where('sales_orders.tenant_id', $tenantId)
            ->where('sales_orders.order_date', '>=', now()->subDays(30))
            ->groupBy('inventory_items.id', 'inventory_items.sku', 'inventory_items.product_name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        $lowStockItems = InventoryItem::query()
            ->whereRaw('(current_quantity - reserved_quantity) <= minimum_stock')
            ->select('id', 'sku', 'product_name', 'current_quantity', 'reserved_quantity', 'minimum_stock')
            ->limit(5)
            ->get();

        $lowStockMaterials = Material::query()
            ->whereColumn('stock_quantity', '<=', 'min_stock')
            ->select('id', 'code', 'name', 'stock_quantity', 'unit', 'min_stock')
            ->limit(5)
            ->get();

        $recentProductions = StockAdjustment::query()
            ->where('adjustment_type', StockAdjustment::TYPE_PRODUCTION_ENTRY)
            ->whereNotNull('batch_id')
            ->with('inventoryItem:id,product_name')
            ->select('batch_id', 'inventory_item_id', 'created_at')
            ->selectRaw('SUM(adjustment_quantity) as total_qty')
            ->groupBy('batch_id', 'inventory_item_id', 'created_at')
            ->latest('created_at')
            ->limit(5)
            ->get();

        return Inertia::render('HomemadeDashboard', [
            'stats' => $stats,
            'salesTrend' => $salesTrend,
            'topProducts' => $topProducts,
            'lowStockItems' => $lowStockItems,
            'lowStockMaterials' => $lowStockMaterials,
            'recentProductions' => $recentProductions,
        ]);
    }

    private function serviceDashboard(int $tenantId): Response
    {
        $today = now()->toDateString();
        $thisMonth = now()->month;
        $thisYear = now()->year;

        $stats = [
            'sales_today' => SalesOrder::query()
                ->whereDate('order_date', $today)
                ->sum('total_amount'),
            'sales_today_count' => SalesOrder::query()
                ->whereDate('order_date', $today)
                ->count(),
            'sales_month' => SalesOrder::query()
                ->whereMonth('order_date', $thisMonth)
                ->whereYear('order_date', $thisYear)
                ->sum('total_amount'),
            'sales_month_count' => SalesOrder::query()
                ->whereMonth('order_date', $thisMonth)
                ->whereYear('order_date', $thisYear)
                ->count(),
            'total_services' => Service::query()->count(),
            'outstanding_receivables' => SalesOrder::query()
                ->where('payment_status', '!=', 'paid')
                ->where('status', '!=', 'cancelled')
                ->sum(DB::raw('total_amount - paid_amount')),
        ];

        $salesTrend = SalesOrder::query()
            ->select(
                DB::raw('DATE(order_date) as date'),
                DB::raw('SUM(total_amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->where('order_date', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $topServices = DB::table('sales_order_items')
            ->join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.id')
            ->join('services', 'sales_order_items.service_id', '=', 'services.id')
            ->select(
                'services.code as sku',
                'services.name as name',
                DB::raw('SUM(sales_order_items.quantity) as total_sold'),
                DB::raw('SUM(sales_order_items.subtotal) as total_revenue')
            )
            ->where('sales_orders.tenant_id', $tenantId)
            ->where('sales_orders.order_date', '>=', now()->subDays(30))
            ->groupBy('services.id', 'services.code', 'services.name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        return Inertia::render('ServiceDashboard', [
            'stats' => $stats,
            'salesTrend' => $salesTrend,
            'topProducts' => $topServices,
        ]);
    }
}
