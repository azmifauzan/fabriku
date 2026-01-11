<?php

namespace App\Observers;

use App\Models\InventoryItem;
use Illuminate\Support\Facades\Log;

class InventoryObserver
{
    /**
     * Handle the InventoryItem "saved" event.
     * Check for low stock and expiry alerts.
     */
    public function saved(InventoryItem $item): void
    {
        // Check for low stock alert
        if ($item->isLowStock()) {
            $this->triggerLowStockAlert($item);
        }

        // Check for expiring soon (food items only)
        if ($item->category === 'food' && $item->expiry_date) {
            if ($item->isExpiringSoon(7)) {
                $this->triggerExpiryAlert($item);
            }

            // Auto-update status if expired
            if ($item->expiry_date->isPast() && $item->status !== 'expired') {
                $item->status = 'expired';
                $item->saveQuietly(); // Prevent infinite loop
            }
        }
    }

    /**
     * Trigger low stock alert (log for now, can be email/notification later)
     */
    protected function triggerLowStockAlert(InventoryItem $item): void
    {
        Log::warning('Low stock alert', [
            'tenant_id' => $item->tenant_id,
            'item_id' => $item->id,
            'sku' => $item->sku,
            'name' => $item->name,
            'current_stock' => $item->current_stock,
            'minimum_stock' => $item->minimum_stock,
            'available_stock' => $item->getAvailableStockAttribute(),
        ]);

        // TODO: Send email notification to admin
        // TODO: Create in-app notification
        // TODO: Push to WhatsApp (Phase 2)
    }

    /**
     * Trigger expiry alert for food items
     */
    protected function triggerExpiryAlert(InventoryItem $item): void
    {
        $daysUntilExpiry = now()->diffInDays($item->expiry_date, false);

        Log::warning('Expiry alert', [
            'tenant_id' => $item->tenant_id,
            'item_id' => $item->id,
            'sku' => $item->sku,
            'name' => $item->name,
            'expiry_date' => $item->expiry_date->toDateString(),
            'days_until_expiry' => $daysUntilExpiry,
            'current_stock' => $item->current_stock,
        ]);

        // TODO: Send urgent email notification
        // TODO: Create in-app notification with urgency flag
        // TODO: Push to WhatsApp (Phase 2)
    }

    /**
     * Handle the InventoryItem "deleting" event.
     * Prevent deletion if item has reserved stock or related sales.
     */
    public function deleting(InventoryItem $item): bool
    {
        // Check if item has reserved stock
        if ($item->reserved_stock > 0) {
            Log::warning('Attempted to delete inventory item with reserved stock', [
                'tenant_id' => $item->tenant_id,
                'item_id' => $item->id,
                'sku' => $item->sku,
                'reserved_stock' => $item->reserved_stock,
            ]);

            // Prevent deletion
            return false;
        }

        // TODO: Check if item has related sales orders (Phase 6)
        // TODO: Create audit trail entry (Phase 2)

        return true;
    }
}
