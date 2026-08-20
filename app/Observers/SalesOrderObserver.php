<?php

namespace App\Observers;

use App\Exceptions\InsufficientStockException;
use App\Models\SalesOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SalesOrderObserver
{
    /**
     * Handle the SalesOrder "updated" event.
     */
    public function updated(SalesOrder $salesOrder): void
    {
        // Handle status changes
        if ($salesOrder->isDirty('status')) {
            $originalStatus = $salesOrder->getOriginal('status');
            $newStatus = $salesOrder->status;

            Log::info("SalesOrder status changed: {$originalStatus} -> {$newStatus}", ['id' => $salesOrder->id]);

            // Draft -> Confirmed/Processing: Reserve Stock
            if ($originalStatus === 'draft' && in_array($newStatus, ['confirmed', 'processing'])) {
                $this->reserveStock($salesOrder);
            }

            // Confirmed/Processing/Shipped -> Completed: Deduct Stock
            elseif (in_array($originalStatus, ['confirmed', 'processing', 'shipped']) && $newStatus === 'completed') {
                $this->deductStock($salesOrder);
            }

            // Confirmed/Processing/Shipped -> Cancelled: Release Reserved Stock
            elseif (in_array($originalStatus, ['confirmed', 'processing', 'shipped']) && $newStatus === 'cancelled') {
                $this->releaseReservedStock($salesOrder);
            }

            // Draft -> Cancelled: Do nothing (no stock reserved yet)
            // Draft -> Completed: Should not happen in normal flow, but would need to deduct without reservation
        }
    }

    /**
     * Handle the SalesOrder "deleted" event (Soft Delete).
     */
    public function deleted(SalesOrder $salesOrder): void
    {
        // If order was confirmed/processing/shipped, release reserved stock
        if (in_array($salesOrder->status, ['confirmed', 'processing', 'shipped'])) {
            $this->releaseReservedStock($salesOrder);
        }
    }

    /**
     * Handle the SalesOrder "restored" event.
     */
    public function restored(SalesOrder $salesOrder): void
    {
        // If order is confirmed/processing/shipped, reserve stock again
        if (in_array($salesOrder->status, ['confirmed', 'processing', 'shipped'])) {
            $this->reserveStock($salesOrder);
        }
    }

    /**
     * Handle the SalesOrder "force deleting" event.
     */
    public function forceDeleting(SalesOrder $salesOrder): void
    {
        // Only release stock if it wasn't already released by a soft delete
        // If the order is already trashed (soft deleted), stock was released in 'deleted' event.
        // We only care if we are force deleting a NON-trashed order (direct hard delete).
        if (! $salesOrder->trashed() && in_array($salesOrder->status, ['confirmed', 'processing', 'shipped'])) {
            $this->releaseReservedStock($salesOrder);
        }
    }

    protected function reserveStock(SalesOrder $salesOrder): void
    {
        DB::transaction(function () use ($salesOrder) {
            foreach ($salesOrder->items()->get() as $item) {
                if ($item->inventory_item_id) {
                    if (! $item->inventoryItem->reserveStock($item->quantity)) {
                        throw new InsufficientStockException(
                            "Stok tidak cukup untuk reservasi item {$item->inventoryItem->sku} (butuh {$item->quantity}) pada pesanan {$salesOrder->order_number}."
                        );
                    }
                }
            }
        });
    }

    protected function deductStock(SalesOrder $salesOrder): void
    {
        DB::transaction(function () use ($salesOrder) {
            foreach ($salesOrder->items()->get() as $item) {
                if ($item->inventory_item_id) {
                    // Deduct from current_quantity AND reserved_quantity
                    $inventoryItem = $item->inventoryItem;

                    // Reduce reserved quantity (releasing the reservation)
                    if (! $inventoryItem->releaseReservedStock($item->quantity)) {
                        throw new InsufficientStockException(
                            "Reserved stock tidak cukup untuk item {$inventoryItem->sku} (butuh {$item->quantity}) pada pesanan {$salesOrder->order_number}."
                        );
                    }

                    // Reduce actual quantity (shipping the item)
                    if (! $inventoryItem->deductStock($item->quantity)) {
                        throw new InsufficientStockException(
                            "Stok fisik tidak cukup untuk item {$inventoryItem->sku} (butuh {$item->quantity}) pada pesanan {$salesOrder->order_number}."
                        );
                    }
                }
            }
        });
    }

    protected function releaseReservedStock(SalesOrder $salesOrder): void
    {
        DB::transaction(function () use ($salesOrder) {
            foreach ($salesOrder->items()->get() as $item) {
                if ($item->inventory_item_id) {
                    if (! $item->inventoryItem->releaseReservedStock($item->quantity)) {
                        throw new InsufficientStockException(
                            "Reserved stock tidak cukup untuk item {$item->inventoryItem->sku} (butuh {$item->quantity}) pada pesanan {$salesOrder->order_number}."
                        );
                    }
                }
            }
        });
    }
}
