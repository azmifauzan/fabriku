<?php

namespace App\Http\Controllers;

use App\Models\InventoryLocation;
use Inertia\Inertia;

class InventoryVisualizationController extends Controller
{
    public function index()
    {
        $locations = InventoryLocation::query()
            ->active()
            ->with(['inventoryItems' => function ($query) {
                $query->select('id', 'location_id', 'sku', 'product_name', 'current_quantity', 'reserved_quantity', 'minimum_stock', 'image_path')
                    ->where('current_quantity', '>', 0)
                    ->orderBy('product_name');
            }])
            ->withSum('inventoryItems', 'current_quantity')
            ->orderBy('code')
            ->get()
            ->map(function ($location) {
                $usedCapacity = $location->inventory_items_sum_current_quantity ?? 0;

                return [
                    'id' => $location->id,
                    'name' => $location->name,
                    'code' => $location->code,
                    'type' => $location->type,
                    'capacity' => $location->capacity,
                    'used_capacity' => $usedCapacity,
                    'percentage' => $location->capacity ? round(($usedCapacity / $location->capacity) * 100) : 0,
                    'is_unlimited' => is_null($location->capacity),
                    'items' => $location->inventoryItems->map(fn ($item) => [
                        'id' => $item->id,
                        'sku' => $item->sku,
                        'name' => $item->product_name,
                        'quantity' => $item->current_quantity,
                        'reserved' => $item->reserved_quantity,
                        'is_low_stock' => $item->current_quantity <= $item->minimum_stock,
                        'image_url' => $item->image_url,
                    ]),
                ];
            });

        return Inertia::render('Inventory/Visualization', [
            'locations' => $locations,
            'stats' => [
                'total_locations' => $locations->count(),
                'total_items' => $locations->sum(fn ($l) => $l['items']->count()),
                'total_capacity' => $locations->sum('capacity'), // nulls ignored, might be misleading if mixing unlimited
                'total_used' => $locations->sum('used_capacity'),
            ],
        ]);
    }
}
