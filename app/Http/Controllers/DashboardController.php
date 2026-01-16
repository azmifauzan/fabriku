<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\Material;
use App\Models\PreparationOrder;
use App\Models\ProductionOrder;
use App\Models\SalesOrder;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $tenantId = auth()->user()->tenant_id;

        // KPI Cards
        $stats = [
            'total_materials' => Material::query()->count(),
            'low_stock_materials' => Material::query()
                ->whereRaw('stock_quantity <= min_stock')
                ->count(),
            'total_inventory' => InventoryItem::query()->sum('current_quantity'),
            'low_stock_inventory' => InventoryItem::query()
                ->whereRaw('(current_quantity - reserved_quantity) <= minimum_stock')
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
                'inventory_items.name',
                DB::raw('SUM(sales_order_items.quantity) as total_sold'),
                DB::raw('SUM(sales_order_items.subtotal) as total_revenue')
            )
            ->where('sales_orders.tenant_id', $tenantId)
            ->where('sales_orders.order_date', '>=', now()->subDays(30))
            ->groupBy('inventory_items.id', 'inventory_items.sku', 'inventory_items.name')
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
            ->select('id', 'order_number', 'preparation_order_id', 'quantity_produced', 'status', 'created_at')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($order) => [
                'type' => 'production',
                'description' => "Production {$order->order_number}".
                    ($order->preparationOrder?->pattern ? " - {$order->preparationOrder->pattern->name}" : ''),
                'quantity' => $order->quantity_produced,
                'status' => $order->status,
                'date' => $order->created_at,
            ]);

        $recentActivities = $recentSales->concat($recentProduction)
            ->sortByDesc('date')
            ->take(10)
            ->values();

        // Low Stock Alerts
        $lowStockMaterials = Material::query()
            ->whereRaw('stock_quantity <= min_stock')
            ->select('id', 'code', 'name', 'stock_quantity', 'unit', 'min_stock')
            ->limit(5)
            ->get();

        $lowStockInventory = InventoryItem::query()
            ->whereRaw('(current_quantity - reserved_quantity) <= minimum_stock')
            ->select('id', 'sku', 'product_name', 'current_quantity', 'reserved_quantity', 'minimum_stock')
            ->limit(5)
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'salesTrend' => $salesTrend,
            'topProducts' => $topProducts,
            'recentActivities' => $recentActivities,
            'lowStockMaterials' => $lowStockMaterials,
            'lowStockInventory' => $lowStockInventory,
        ]);
    }
}
