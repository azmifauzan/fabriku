<?php

namespace Database\Factories;

use App\Models\InventoryItem;
use App\Models\StockAdjustment;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockAdjustment>
 */
class StockAdjustmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantityBefore = fake()->numberBetween(50, 200);
        $adjustmentQuantity = fake()->numberBetween(-30, 50);
        $quantityAfter = max(0, $quantityBefore + $adjustmentQuantity);

        return [
            'tenant_id' => Tenant::factory(),
            'inventory_item_id' => InventoryItem::factory(),
            'adjustment_type' => fake()->randomElement(StockAdjustment::getAdjustmentTypes()),
            'quantity_before' => $quantityBefore,
            'quantity_after' => $quantityAfter,
            'adjustment_quantity' => $quantityAfter - $quantityBefore,
            'reason' => fake()->sentence(),
            'notes' => fake()->optional()->paragraph(),
            'adjusted_by' => User::factory(),
        ];
    }

    /**
     * Opening balance adjustment
     */
    public function openingBalance(): static
    {
        return $this->state(fn (array $attributes) => [
            'adjustment_type' => StockAdjustment::TYPE_OPENING_BALANCE,
            'quantity_before' => 0,
        ]);
    }

    /**
     * Correction adjustment
     */
    public function correction(): static
    {
        return $this->state(fn (array $attributes) => [
            'adjustment_type' => StockAdjustment::TYPE_CORRECTION,
        ]);
    }
}
