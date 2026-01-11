<?php

namespace Database\Factories;

use App\Models\CuttingOrder;
use App\Models\Material;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CuttingResult>
 */
class CuttingResultFactory extends Factory
{
    public function definition(): array
    {
        $actualQuantity = fake()->numberBetween(50, 200);
        $defectQuantity = fake()->numberBetween(0, (int) ($actualQuantity * 0.05)); // Up to 5% defects
        $materialUsed = fake()->randomFloat(2, 5, 50); // Material used in meters/yards
        $materialWasted = $materialUsed * fake()->randomFloat(2, 0.02, 0.08); // 2-8% waste

        return [
            'tenant_id' => Tenant::factory(),
            'cutting_order_id' => CuttingOrder::factory(),
            'material_id' => Material::factory(),
            'material_used' => $materialUsed,
            'material_wasted' => $materialWasted,
            'waste_percentage' => 0, // Will be calculated by model
            'actual_quantity' => $actualQuantity,
            'defect_quantity' => $defectQuantity,
            'efficiency_percentage' => 0, // Will be calculated by model
            'notes' => fake()->optional(0.3)->sentence(),
        ];
    }

    public function forTenant(int $tenantId): self
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => $tenantId,
        ]);
    }

    public function forCuttingOrder(int $cuttingOrderId): self
    {
        return $this->state(fn (array $attributes) => [
            'cutting_order_id' => $cuttingOrderId,
        ]);
    }
}
