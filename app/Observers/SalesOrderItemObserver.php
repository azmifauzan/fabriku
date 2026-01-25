<?php

namespace App\Observers;

use App\Models\SalesOrderItem;

class SalesOrderItemObserver
{
    /**
     * Handle the SalesOrderItem "created" event.
     */
    public function created(SalesOrderItem $item): void
    {
        $salesOrder = $item->salesOrder;

        // If order is already confirmed/processing, reserve stock for this new item
        if (in_array($salesOrder->status, ['confirmed', 'processing'])) {
            $item->inventoryItem->increment('reserved_quantity', $item->quantity);
        }
    }

    /**
     * Handle the SalesOrderItem "updated" event.
     */
    public function updated(SalesOrderItem $item): void
    {
        $salesOrder = $item->salesOrder;

        // If order is confirmed/processing, adjust reserved stock
        if (in_array($salesOrder->status, ['confirmed', 'processing'])) {
            if ($item->isDirty('quantity')) {
                $difference = $item->quantity - $item->getOriginal('quantity');
                
                if ($difference > 0) {
                    $item->inventoryItem->increment('reserved_quantity', $difference);
                } else {
                    $item->inventoryItem->decrement('reserved_quantity', abs($difference));
                }
            }
        }
    }

    /**
     * Handle the SalesOrderItem "deleted" event.
     */
    public function deleted(SalesOrderItem $item): void
    {
        $salesOrder = $item->salesOrder;

        // If order is confirmed/processing, release reserved stock
        if (in_array($salesOrder->status, ['confirmed', 'processing'])) {
            $item->inventoryItem->decrement('reserved_quantity', $item->quantity);
        }
    }
}
