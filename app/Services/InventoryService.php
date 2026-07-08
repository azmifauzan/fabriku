<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\InventoryItemCategory;
use App\Models\InventoryLocation;
use App\Models\Pattern;
use App\Models\ProductionOrder;
use App\Models\StockAdjustment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Get inventory dashboard statistics
     */
    public function getDashboardStats(): array
    {
        return [
            'total_items' => InventoryItem::count(),
            'total_locations' => InventoryLocation::count(),
            'total_stock_value' => InventoryItem::sum(DB::raw('current_quantity * unit_cost')),
            'available_stock_count' => InventoryItem::sum('current_quantity'),
            'reserved_stock_count' => InventoryItem::sum('reserved_quantity'),
            'low_stock_items' => InventoryItem::lowStock()->count(),
            'expiring_soon_items' => InventoryItem::expiring(7)->count(),
            'expired_items' => InventoryItem::expired()->count(),
        ];
    }

    /**
     * Get low stock alerts
     */
    public function getLowStockAlerts(): Collection
    {
        return InventoryItem::lowStock()
            ->with(['inventoryLocation'])
            ->orderBy('current_quantity')
            ->get();
    }

    /**
     * Get expiry alerts (for food items)
     */
    public function getExpiryAlerts(int $days = 7): Collection
    {
        return InventoryItem::expiring($days)
            ->with(['inventoryLocation'])
            ->orderBy('expired_date')
            ->get();
    }

    /**
     * Get expired items
     */
    public function getExpiredItems(): Collection
    {
        return InventoryItem::expired()
            ->with(['inventoryLocation'])
            ->orderBy('expired_date')
            ->get();
    }

    /**
     * Transfer stock to multiple locations
     *
     * @param  array<int, array{location_id:int, quantity:int}>  $splits
     */
    public function transferStock(InventoryItem $item, array $splits, string $reason, ?string $notes = null): array
    {
        return DB::transaction(function () use ($item, $splits, $reason, $notes) {
            $locationIds = array_column($splits, 'location_id');
            $locations = InventoryLocation::whereIn('id', $locationIds)->lockForUpdate()->get()->keyBy('id');

            $item = InventoryItem::where('id', $item->id)->lockForUpdate()->first();

            if (! $item) {
                throw new \Exception('Item tidak ditemukan atau stoknya sudah dipindahkan sepenuhnya.');
            }

            $totalQuantity = array_sum(array_column($splits, 'quantity'));

            // Re-validate under the lock: the FormRequest already checked this,
            // but that check ran before the transaction/lock, so a concurrent
            // reservation or transfer landing in between must be re-checked here.
            if ($totalQuantity > $item->available_stock) {
                throw new \Exception("Total yang dipindah ({$totalQuantity}) melebihi stock tersedia ({$item->available_stock}).");
            }

            foreach ($splits as $split) {
                $location = $locations->get($split['location_id']);

                if ($location && $location->capacity !== null && $split['quantity'] > $location->available_capacity) {
                    throw new \Exception("Rak {$location->name} tidak cukup kapasitas (sisa: {$location->available_capacity}).");
                }
            }

            $this->adjustStock($item, 'subtract', $totalQuantity, StockAdjustment::TYPE_TRANSFER, $reason, $notes);

            $createdItems = [];
            foreach ($splits as $split) {
                $newItem = InventoryItem::create([
                    'tenant_id' => $item->tenant_id,
                    'product_name' => $item->product_name,
                    'product_code' => $item->product_code,
                    'unit_cost' => $item->unit_cost,
                    'selling_price' => $item->selling_price,
                    'category_id' => $item->category_id,
                    'production_order_id' => $item->production_order_id,
                    'source_type' => $item->source_type,
                    'location_id' => $split['location_id'],
                    'current_quantity' => 0,
                    'target_quantity' => $item->target_quantity,
                    'minimum_stock' => $item->minimum_stock,
                    'quality_grade' => $item->quality_grade,
                    'status' => $item->status,
                    'expired_date' => $item->expired_date,
                    'notes' => $item->notes,
                    'image_path' => $item->image_path,
                ]);

                $this->adjustStock($newItem, 'add', $split['quantity'], StockAdjustment::TYPE_TRANSFER, $reason, $notes);
                $createdItems[] = $newItem;
            }

            if ($item->current_quantity === 0 && $item->reserved_quantity === 0) {
                $item->delete();
            }

            return $createdItems;
        });
    }

    /**
     * Adjust stock for item
     */
    public function adjustStock(InventoryItem $item, string $type, int $quantity, string $adjustmentType, string $reason, ?string $notes = null): StockAdjustment
    {
        return DB::transaction(function () use ($item, $type, $quantity, $adjustmentType, $reason, $notes) {
            $oldStock = $item->current_quantity;
            $adjustmentQuantity = 0;

            switch ($type) {
                case 'add':
                    $item->increment('current_quantity', $quantity);
                    $adjustmentQuantity = $quantity;
                    break;
                case 'subtract':
                    if ($item->current_quantity < $quantity) {
                        throw new \Exception('Insufficient stock for subtraction.');
                    }
                    $item->decrement('current_quantity', $quantity);
                    $adjustmentQuantity = -$quantity;
                    break;
                case 'set':
                    $adjustmentQuantity = $quantity - $item->current_quantity;
                    $item->update(['current_quantity' => $quantity]);
                    break;
                default:
                    throw new \Exception('Invalid adjustment type.');
            }

            // Create stock adjustment record for audit trail
            return StockAdjustment::create([
                'inventory_item_id' => $item->id,
                'adjustment_type' => $adjustmentType,
                'quantity_before' => $oldStock,
                'quantity_after' => $item->fresh()->current_quantity,
                'adjustment_quantity' => $adjustmentQuantity,
                'reason' => $reason,
                'notes' => $notes,
            ]);
        });
    }

    /**
     * Record opening balance adjustment (for manual entry items)
     */
    public function recordOpeningBalance(InventoryItem $item, int $quantity, string $reason, ?string $notes = null): StockAdjustment
    {
        if ($item->isFromProduction()) {
            throw new \Exception('Opening balance hanya bisa untuk item manual entry.');
        }

        return $this->adjustStock(
            $item,
            'set',
            $quantity,
            StockAdjustment::TYPE_OPENING_BALANCE,
            $reason,
            $notes
        );
    }

    /**
     * Record correction adjustment
     */
    public function recordCorrection(InventoryItem $item, int $newQuantity, string $reason, ?string $notes = null): StockAdjustment
    {
        return $this->adjustStock(
            $item,
            'set',
            $newQuantity,
            StockAdjustment::TYPE_CORRECTION,
            $reason,
            $notes
        );
    }

    /**
     * Get adjustment history for an item
     */
    public function getAdjustmentHistory(InventoryItem $item): Collection
    {
        return $item->stockAdjustments()
            ->with(['adjustedBy', 'approvedBy'])
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Get adjustment statistics
     */
    public function getAdjustmentStats(): array
    {
        return [
            'total_adjustments' => StockAdjustment::count(),
            'adjustments_this_month' => StockAdjustment::whereMonth('created_at', now()->month)->count(),
            'by_type' => StockAdjustment::selectRaw('adjustment_type, COUNT(*) as count')
                ->groupBy('adjustment_type')
                ->pluck('count', 'adjustment_type')
                ->toArray(),
            'recent_adjustments' => StockAdjustment::with(['inventoryItem', 'adjustedBy'])
                ->orderByDesc('created_at')
                ->limit(10)
                ->get(),
        ];
    }

    /**
     * Reserve stock for sales order
     */
    public function reserveStock(InventoryItem $item, int $quantity): bool
    {
        if ($item->available_stock < $quantity) {
            return false;
        }

        $item->increment('reserved_quantity', $quantity);

        return true;
    }

    /**
     * Release reserved stock
     */
    public function releaseReservedStock(InventoryItem $item, int $quantity): bool
    {
        if ($item->reserved_quantity < $quantity) {
            return false;
        }

        $item->decrement('reserved_quantity', $quantity);

        return true;
    }

    /**
     * Consume stock (for sales)
     */
    public function consumeStock(InventoryItem $item, int $quantity): bool
    {
        if ($item->current_quantity < $quantity) {
            return false;
        }

        DB::transaction(function () use ($item, $quantity) {
            $item->decrement('current_quantity', $quantity);

            // If there was reserved stock, reduce it proportionally
            if ($item->reserved_quantity > 0) {
                $toRelease = min($quantity, $item->reserved_quantity);
                $item->decrement('reserved_quantity', $toRelease);
            }
        });

        return true;
    }

    /**
     * Get inventory valuation report
     */
    public function getValuationReport(): array
    {
        $items = InventoryItem::with(['inventoryLocation', 'productionOrder.preparationOrder.pattern'])
            ->where('current_quantity', '>', 0)
            ->get();

        $totalValue = 0;

        foreach ($items as $item) {
            $itemValue = $item->current_quantity * $item->unit_cost;
            $totalValue += $itemValue;
        }

        return [
            'total_value' => $totalValue,
            'total_items' => $items->count(),
            'total_stock' => $items->sum('current_quantity'),
            'top_value_items' => $items->sortByDesc(function ($item) {
                return $item->current_quantity * $item->unit_cost;
            })->take(10)->values(),
        ];
    }

    /**
     * Clean up expired items (mark as expired)
     */
    public function markExpiredItems(): int
    {
        $count = 0;

        InventoryItem::whereNotNull('expired_date')
            ->where('expired_date', '<', now())
            ->where('status', '!=', 'expired')
            ->chunkById(100, function ($items) use (&$count) {
                foreach ($items as $item) {
                    $item->update(['status' => 'expired']);
                    $count++;
                }
            });

        return $count;
    }

    /**
     * Suggest optimal locations for item based on category and capacity
     */
    public function suggestLocations(string $category, int $stockQuantity): Collection
    {
        return InventoryLocation::available()
            ->where(function ($query) use ($stockQuantity) {
                $query->whereNull('capacity')
                    ->orWhere('capacity', '>=', $stockQuantity);
            })
            ->withCount('inventoryItems')
            ->orderBy('inventory_items_count') // Prefer less crowded locations
            ->limit(5)
            ->get();
    }

    /**
     * Get items that need attention (low stock, expiring, damaged)
     */
    public function getItemsNeedingAttention(): array
    {
        return [
            'low_stock' => $this->getLowStockAlerts(),
            'expiring_soon' => $this->getExpiryAlerts(7),
            'expired' => $this->getExpiredItems(),
            'damaged' => InventoryItem::where('status', 'damaged')->get(),
        ];
    }

    /**
     * Get related data required for create or edit forms of InventoryItem
     */
    public function getFormDataForCreateOrEdit(?InventoryItem $item = null): array
    {
        $locations = InventoryLocation::active()->orderBy('name')->get(['id', 'name', 'code', 'capacity']);
        $locations->each(function (InventoryLocation $location) {
            $location->available_capacity = $location->available_capacity;
        });
        $patterns = Pattern::orderBy('name')->get(['id', 'name', 'code', 'output_quantity']);
        $categories = InventoryItemCategory::active()->orderBy('sort_order')->orderBy('name')->get(['id', 'name']);

        $productionOrdersQuery = ProductionOrder::whereIn('status', ['completed', 'sent'])
            ->with(['preparationOrder' => function ($query) {
                $query->select('id', 'pattern_id', 'output_quantity', 'output_unit')
                    ->with(['materialUsages.materialReceipt']);
            }, 'preparationOrder.pattern'])
            ->orderByRaw('COALESCE(completed_date, estimated_completion_date) DESC');

        if ($item) {
            $productionOrdersQuery->where(function ($query) use ($item) {
                $query->whereDoesntHave('inventoryItems')
                    ->orWhere('id', $item->production_order_id);
            });
        } else {
            $productionOrdersQuery->whereDoesntHave('inventoryItems');
        }

        $productionOrders = $productionOrdersQuery->get(['id', 'order_number', 'preparation_order_id', 'labor_cost', 'completed_date', 'estimated_completion_date', 'status'])
            ->map(function ($order) {
                $materialCost = $order->preparationOrder?->materialUsages->sum(function ($usage) {
                    return $usage->quantity * ($usage->materialReceipt->price_per_unit ?? 0);
                }) ?? 0;
                $order->material_cost = $materialCost;

                return $order;
            });

        return [
            'locations' => $locations,
            'patterns' => $patterns,
            'productionOrders' => $productionOrders,
            'categories' => $categories,
        ];
    }
}
