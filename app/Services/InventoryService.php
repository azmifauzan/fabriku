<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\InventoryLocation;
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
            'total_stock_value' => InventoryItem::sum(DB::raw('current_stock * unit_cost')),
            'available_stock_count' => InventoryItem::sum('current_stock'),
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
            ->orderBy('current_stock')
            ->get();
    }

    /**
     * Get expiry alerts (for food items)
     */
    public function getExpiryAlerts(int $days = 7): Collection
    {
        return InventoryItem::where('category', 'food')
            ->expiring($days)
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
     * Move item to different location
     */
    public function moveItem(InventoryItem $item, InventoryLocation $newLocation, ?string $reason = null): bool
    {
        // Check if new location has capacity
        if ($newLocation->capacity && $newLocation->available_capacity < $item->current_stock) {
            throw new \Exception('Location does not have sufficient capacity.');
        }

        $oldLocationId = $item->inventory_location_id;

        DB::transaction(function () use ($item, $newLocation) {
            $item->update(['inventory_location_id' => $newLocation->id]);

            // Log the movement (implement StockMovement model later if needed)
            // StockMovement::create([
            //     'inventory_item_id' => $item->id,
            //     'from_location_id' => $oldLocationId,
            //     'to_location_id' => $newLocation->id,
            //     'type' => 'movement',
            //     'reason' => $reason,
            // ]);
        });

        return true;
    }

    /**
     * Adjust stock for item
     */
    public function adjustStock(InventoryItem $item, string $type, int $quantity, string $reason): bool
    {
        DB::transaction(function () use ($item, $type, $quantity) {
            $oldStock = $item->current_stock;

            switch ($type) {
                case 'add':
                    $item->increment('current_stock', $quantity);
                    break;
                case 'subtract':
                    if ($item->current_stock < $quantity) {
                        throw new \Exception('Insufficient stock for subtraction.');
                    }
                    $item->decrement('current_stock', $quantity);
                    break;
                case 'set':
                    $item->update(['current_stock' => $quantity]);
                    break;
                default:
                    throw new \Exception('Invalid adjustment type.');
            }

            // Log the adjustment
            // StockMovement::create([
            //     'inventory_item_id' => $item->id,
            //     'type' => 'adjustment',
            //     'quantity_before' => $oldStock,
            //     'quantity_after' => $item->fresh()->current_stock,
            //     'adjustment_type' => $type,
            //     'reason' => $reason,
            // ]);
        });

        return true;
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
        if ($item->current_stock < $quantity) {
            return false;
        }

        DB::transaction(function () use ($item, $quantity) {
            $item->decrement('current_stock', $quantity);

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
        $items = InventoryItem::with(['inventoryLocation', 'pattern'])
            ->where('current_stock', '>', 0)
            ->get();

        $totalValue = 0;
        $categoryBreakdown = [];

        foreach ($items as $item) {
            $itemValue = $item->current_stock * $item->unit_cost;
            $totalValue += $itemValue;

            if (! isset($categoryBreakdown[$item->category])) {
                $categoryBreakdown[$item->category] = [
                    'items' => 0,
                    'stock' => 0,
                    'value' => 0,
                ];
            }

            $categoryBreakdown[$item->category]['items']++;
            $categoryBreakdown[$item->category]['stock'] += $item->current_stock;
            $categoryBreakdown[$item->category]['value'] += $itemValue;
        }

        return [
            'total_value' => $totalValue,
            'total_items' => $items->count(),
            'total_stock' => $items->sum('current_stock'),
            'category_breakdown' => $categoryBreakdown,
            'top_value_items' => $items->sortByDesc(function ($item) {
                return $item->current_stock * $item->unit_cost;
            })->take(10)->values(),
        ];
    }

    /**
     * Clean up expired items (mark as expired)
     */
    public function markExpiredItems(): int
    {
        $count = 0;

        InventoryItem::where('category', 'food')
            ->whereNotNull('expired_date')
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
}
