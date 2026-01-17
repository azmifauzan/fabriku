<?php

namespace App\Services;

use App\Models\Material;
use App\Models\PreparationOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MaterialStockService
{
    /**
     * Deduct material stock based on preparation order material_usage
     */
    public function deductStock(PreparationOrder $order): void
    {
        DB::transaction(function () use ($order) {
            $materialsUsed = $order->material_usage;

            if (! is_array($materialsUsed)) {
                Log::warning("PreparationOrder {$order->id} has invalid material_usage format");

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
                $material->stock_quantity -= $quantityUsed;

                // Ensure stock doesn't go below 0
                if ($material->stock_quantity < 0) {
                    $material->stock_quantity = 0;
                    Log::warning("Material {$material->code} stock went negative, set to 0");
                }

                $material->save();

                Log::info("Deducted {$quantityUsed} {$materialData['unit']} from Material {$material->code}. New stock: {$material->stock_quantity}");
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
