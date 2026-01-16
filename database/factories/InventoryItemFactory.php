<?php

namespace Database\Factories;

use App\Models\InventoryLocation;
use App\Models\ProductionOrder;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InventoryItem>
 */
class InventoryItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $targetQuantity = fake()->numberBetween(50, 500);
        $currentStock = fake()->numberBetween(0, $targetQuantity);
        $reservedStock = fake()->numberBetween(0, min($currentStock, 50));

        return [
            'tenant_id' => Tenant::factory(),
            'sku' => fake()->unique()->regexify('[A-Z]{2}[0-9]{6}'),
            'production_order_id' => ProductionOrder::factory(),
            'location_id' => InventoryLocation::factory(),
            'product_name' => fake()->words(3, true),
            'product_code' => fake()->optional()->bothify('PRD-###'),
            'target_quantity' => $targetQuantity,
            'current_quantity' => $currentStock,
            'reserved_quantity' => $reservedStock,
            'quality_grade' => fake()->randomElement(['A', 'B', 'Reject']),
            'unit_cost' => fake()->randomFloat(2, 1, 100),
            'selling_price' => fake()->randomFloat(2, 10, 200),
            'production_date' => fake()->dateTimeBetween('-6 months', 'now'),
            'expired_date' => fake()->optional(0.3)->dateTimeBetween('now', '+1 year'),
            'notes' => fake()->optional(0.4)->sentence(),
        ];
    }

    /**
     * Low stock item
     */
    public function lowStock(): static
    {
        return $this->state(function (array $attributes) {
            $targetQuantity = $attributes['target_quantity'] ?? 100;

            return [
                'current_quantity' => fake()->numberBetween(0, (int) ($targetQuantity * 0.1)),
                'reserved_quantity' => 0,
            ];
        });
    }

    /**
     * Out of stock item
     */
    public function outOfStock(): static
    {
        return $this->state([
            'current_quantity' => 0,
            'reserved_quantity' => 0,
        ]);
    }

    /**
     * High stock item
     */
    public function highStock(): static
    {
        return $this->state([
            'current_quantity' => fake()->numberBetween(1000, 5000),
            'reserved_quantity' => fake()->numberBetween(0, 100),
        ]);
    }

    /**
     * Grade A quality
     */
    public function gradeA(): static
    {
        return $this->state([
            'quality_grade' => 'A',
            'unit_cost' => fake()->randomFloat(2, 50, 100),
            'selling_price' => fake()->randomFloat(2, 100, 200),
        ]);
    }

    /**
     * Grade B quality
     */
    public function gradeB(): static
    {
        return $this->state([
            'quality_grade' => 'B',
            'unit_cost' => fake()->randomFloat(2, 30, 70),
            'selling_price' => fake()->randomFloat(2, 60, 140),
        ]);
    }

    /**
     * Reject quality
     */
    public function reject(): static
    {
        return $this->state([
            'quality_grade' => 'Reject',
            'unit_cost' => fake()->randomFloat(2, 10, 30),
            'selling_price' => fake()->randomFloat(2, 1, 20),
            'notes' => 'Reject quality - '.fake()->sentence(),
        ]);
    }

    /**
     * Reserved stock item
     */
    public function reserved(): static
    {
        return $this->state(function (array $attributes) {
            $currentQuantity = $attributes['current_quantity'] ?? 100;

            return [
                'reserved_quantity' => fake()->numberBetween(1, (int) $currentQuantity),
            ];
        });
    }
}
