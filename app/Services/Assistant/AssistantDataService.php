<?php

namespace App\Services\Assistant;

use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\Material;
use App\Models\PreparationOrder;
use App\Models\ProductionOrder;
use App\Models\SalesOrder;
use Illuminate\Support\Facades\DB;

class AssistantDataService
{
    private int $tenantId;

    public function __construct(int $tenantId)
    {
        $this->tenantId = $tenantId;
    }

    /**
     * Get dashboard summary data
     */
    public function getDashboardSummary(): array
    {
        return [
            'materials' => [
                'total' => Material::count(),
                'low_stock' => Material::whereRaw('stock_quantity <= min_stock')->count(),
            ],
            'inventory' => [
                'total_items' => InventoryItem::count(),
                'total_quantity' => InventoryItem::sum('current_quantity'),
                'low_stock' => InventoryItem::whereRaw('(current_quantity - reserved_quantity) <= minimum_stock')->count(),
            ],
            'sales_this_month' => [
                'total_amount' => SalesOrder::whereMonth('order_date', now()->month)
                    ->whereYear('order_date', now()->year)
                    ->sum('total_amount'),
                'total_orders' => SalesOrder::whereMonth('order_date', now()->month)
                    ->whereYear('order_date', now()->year)
                    ->count(),
            ],
            'production' => [
                'pending' => ProductionOrder::whereIn('status', ['draft', 'in_progress'])->count(),
                'completed_this_month' => ProductionOrder::where('status', 'completed')
                    ->whereMonth('completed_date', now()->month)
                    ->count(),
            ],
            'preparation' => [
                'pending' => PreparationOrder::whereIn('status', ['draft', 'in_progress'])->count(),
            ],
        ];
    }

    /**
     * Get sales summary for a period
     */
    public function getSalesSummary(string $period = 'month'): array
    {
        $query = SalesOrder::query();

        switch ($period) {
            case 'today':
                $query->whereDate('order_date', now()->toDateString());
                break;
            case 'week':
                $query->where('order_date', '>=', now()->startOfWeek());
                break;
            case 'month':
            default:
                $query->whereMonth('order_date', now()->month)
                    ->whereYear('order_date', now()->year);
                break;
            case 'year':
                $query->whereYear('order_date', now()->year);
                break;
        }

        $total = $query->sum('total_amount');
        $count = $query->count();
        $paid = $query->where('payment_status', 'paid')->sum('total_amount');
        $unpaid = $query->where('payment_status', '!=', 'paid')->sum(DB::raw('total_amount - paid_amount'));

        return [
            'period' => $period,
            'total_amount' => $total,
            'total_orders' => $count,
            'average_order' => $count > 0 ? round($total / $count, 2) : 0,
            'paid_amount' => $paid,
            'outstanding' => $unpaid,
        ];
    }

    /**
     * Get low stock materials
     */
    public function getLowStockMaterials(int $limit = 10): array
    {
        return Material::whereRaw('stock_quantity <= min_stock')
            ->select('id', 'code', 'name', 'stock_quantity', 'min_stock', 'unit')
            ->orderByRaw('stock_quantity - min_stock ASC')
            ->limit($limit)
            ->get()
            ->map(fn ($m) => [
                'code' => $m->code,
                'name' => $m->name,
                'stock' => $m->stock_quantity,
                'minimum' => $m->min_stock,
                'unit' => $m->unit,
                'deficit' => $m->min_stock - $m->stock_quantity,
            ])
            ->toArray();
    }

    /**
     * Get low stock inventory items
     */
    public function getLowStockInventory(int $limit = 10): array
    {
        return InventoryItem::whereRaw('(current_quantity - reserved_quantity) <= minimum_stock')
            ->select('id', 'sku', 'product_name', 'current_quantity', 'reserved_quantity', 'minimum_stock')
            ->orderByRaw('(current_quantity - reserved_quantity) - minimum_stock ASC')
            ->limit($limit)
            ->get()
            ->map(fn ($i) => [
                'sku' => $i->sku,
                'name' => $i->product_name,
                'available' => $i->current_quantity - $i->reserved_quantity,
                'minimum' => $i->minimum_stock,
            ])
            ->toArray();
    }

    /**
     * Get pending orders (production/preparation)
     */
    public function getPendingOrders(): array
    {
        $production = ProductionOrder::whereIn('status', ['draft', 'in_progress', 'sent'])
            ->with('preparationOrder:id,order_number')
            ->select('id', 'order_number', 'status', 'type', 'preparation_order_id', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(fn ($p) => [
                'order_number' => $p->order_number,
                'status' => $p->status,
                'type' => $p->type,
                'preparation' => $p->preparationOrder?->order_number,
            ]);

        $preparation = PreparationOrder::whereIn('status', ['draft', 'in_progress'])
            ->select('id', 'order_number', 'status', 'output_quantity', 'output_unit', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(fn ($p) => [
                'order_number' => $p->order_number,
                'status' => $p->status,
                'output' => "{$p->output_quantity} {$p->output_unit}",
            ]);

        return [
            'production' => $production->toArray(),
            'preparation' => $preparation->toArray(),
        ];
    }

    /**
     * Get recent sales orders
     */
    public function getRecentSales(int $limit = 5): array
    {
        return SalesOrder::with('customer:id,name')
            ->select('id', 'order_number', 'customer_id', 'total_amount', 'payment_status', 'status', 'order_date')
            ->orderBy('order_date', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn ($s) => [
                'order_number' => $s->order_number,
                'customer' => $s->customer?->name ?? 'Walk-in',
                'amount' => $s->total_amount,
                'payment_status' => $s->payment_status,
                'status' => $s->status,
                'date' => $s->order_date->format('d M Y'),
            ])
            ->toArray();
    }

    /**
     * Get top customers
     */
    public function getTopCustomers(int $limit = 5): array
    {
        return Customer::withCount('salesOrders')
            ->withSum('salesOrders', 'total_amount')
            ->orderByDesc('sales_orders_sum_total_amount')
            ->limit($limit)
            ->get()
            ->map(fn ($c) => [
                'name' => $c->name,
                'total_orders' => $c->sales_orders_count,
                'total_spent' => $c->sales_orders_sum_total_amount ?? 0,
            ])
            ->toArray();
    }

    /**
     * Search materials by name or code
     */
    public function searchMaterials(string $query): array
    {
        return Material::where('name', 'ilike', "%{$query}%")
            ->orWhere('code', 'ilike', "%{$query}%")
            ->select('id', 'code', 'name', 'stock_quantity', 'unit', 'price_per_unit')
            ->limit(10)
            ->get()
            ->map(fn ($m) => [
                'code' => $m->code,
                'name' => $m->name,
                'stock' => "{$m->stock_quantity} {$m->unit}",
                'price' => $m->price_per_unit,
            ])
            ->toArray();
    }

    /**
     * Get material stock by name
     */
    public function getMaterialStock(string $name): ?array
    {
        $material = Material::where('name', 'ilike', "%{$name}%")
            ->orWhere('code', 'ilike', "%{$name}%")
            ->first();

        if (! $material) {
            return null;
        }

        return [
            'code' => $material->code,
            'name' => $material->name,
            'stock' => $material->stock_quantity,
            'unit' => $material->unit,
            'min_stock' => $material->min_stock,
            'price_per_unit' => $material->price_per_unit,
            'supplier' => $material->supplier_name,
            'is_low_stock' => $material->stock_quantity <= $material->min_stock,
        ];
    }

    /**
     * Compare sales between two periods
     */
    public function compareSales(string $period1 = 'this_month', string $period2 = 'last_month'): array
    {
        $getData = function ($period) {
            $query = SalesOrder::query();

            switch ($period) {
                case 'this_month':
                    $query->whereMonth('order_date', now()->month)
                        ->whereYear('order_date', now()->year);
                    $label = now()->format('F Y');
                    break;
                case 'last_month':
                    $query->whereMonth('order_date', now()->subMonth()->month)
                        ->whereYear('order_date', now()->subMonth()->year);
                    $label = now()->subMonth()->format('F Y');
                    break;
                case 'this_week':
                    $query->where('order_date', '>=', now()->startOfWeek());
                    $label = 'Minggu ini';
                    break;
                case 'last_week':
                    $query->whereBetween('order_date', [
                        now()->subWeek()->startOfWeek(),
                        now()->subWeek()->endOfWeek(),
                    ]);
                    $label = 'Minggu lalu';
                    break;
                default:
                    $label = $period;
            }

            return [
                'label' => $label,
                'total' => $query->sum('total_amount'),
                'count' => $query->count(),
            ];
        };

        $data1 = $getData($period1);
        $data2 = $getData($period2);

        $percentChange = $data2['total'] > 0
            ? round((($data1['total'] - $data2['total']) / $data2['total']) * 100, 1)
            : 0;

        return [
            'current' => $data1,
            'previous' => $data2,
            'change_percent' => $percentChange,
            'change_amount' => $data1['total'] - $data2['total'],
            'trend' => $percentChange > 0 ? 'up' : ($percentChange < 0 ? 'down' : 'stable'),
        ];
    }
}
