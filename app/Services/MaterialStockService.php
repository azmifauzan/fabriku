<?php

namespace App\Services;

use App\Models\Material;
use App\Models\PreparationOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MaterialStockService
{
    /**
     * Deduct material stock based on preparation order materials_used
     */
    public function deductStock(PreparationOrder $order): void
    {
        DB::transaction(function () use ($order) {
            $materialsUsed = $order->materials_used;

            if (! is_array($materialsUsed)) {
                Log::warning("PreparationOrder {$order->id} has invalid materials_used format");

                return;
            }

            foreach ($materialsUsed as $materialData) {
                if (! isset($materialData['material_id'], $materialData['quantity'])) {
                    continue;
                }

                $materialId = $materialData['material_id'];
                $quantityUsed = $materialData['quantity'];

                $material = Material::find($materialId);

                if (! $material) {
                    Log::warning("Material ID {$materialId} not found for PreparationOrder {$order->id}");

                    continue;
                }

                // Deduct stock
                $material->current_stock -= $quantityUsed;

                // Ensure stock doesn't go below 0
                if ($material->current_stock < 0) {
                    $material->current_stock = 0;
                    Log::warning("Material {$material->code} stock went negative, set to 0");
                }

                $material->save();

                Log::info("Deducted {$quantityUsed} {$materialData['unit']} from Material {$material->code}. New stock: {$material->current_stock}");
            }
        });
    }

    /**
     * Check if all materials have sufficient stock
     */
    public function checkStockAvailability(array $materialsUsed): array
    {
        $insufficientMaterials = [];

        foreach ($materialsUsed as $materialData) {
            if (! isset($materialData['material_id'], $materialData['quantity'])) {
                continue;
            }

            $material = Material::find($materialData['material_id']);

            if (! $material) {
                continue;
            }

            if ($material->stock_quantity < $materialData['quantity']) {
                $insufficientMaterials[] = [
                    'material_id' => $material->id,
                    'material_name' => $material->name,
                    'required' => $materialData['quantity'],
                    'available' => $material->stock_quantity,
                    'shortage' => $materialData['quantity'] - $material->stock_quantity,
                ];
            }
        }

        return $insufficientMaterials;
    }
}
