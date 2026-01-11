<?php

namespace Database\Factories;

use App\Models\InventoryLocation;
use App\Models\Pattern;
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
        $category = fake()->randomElement(['garment', 'food']);
        $currentStock = fake()->numberBetween(0, 500);
        $reservedStock = fake()->numberBetween(0, min($currentStock, 50));

        return [
            'tenant_id' => Tenant::factory(),
            'sku' => fake()->unique()->regexify('[A-Z]{2}[0-9]{6}'),
            'name' => fake()->words(3, true),
            'description' => fake()->optional(0.7)->sentence(),
            'category' => $category,
            'pattern_id' => Pattern::factory(),
            'inventory_location_id' => InventoryLocation::factory(),
            'current_stock' => $currentStock,
            'reserved_stock' => $reservedStock,
            'minimum_stock' => fake()->numberBetween(5, 50),
            'unit_cost' => fake()->randomFloat(2, 1, 100),
            'selling_price' => fake()->randomFloat(2, 10, 200),
            'production_date' => fake()->optional(0.8)->dateTimeBetween('-6 months', 'now'),
            'expiry_date' => $category === 'food'
                ? fake()->dateTimeBetween('now', '+1 year')
                : null,
            'best_before_date' => $category === 'food'
                ? fake()->dateTimeBetween('now', '+8 months')
                : null,
            'batch_number' => fake()->optional(0.7)->regexify('BATCH[0-9]{6}'),
            'quality_grade' => fake()->randomElement(['A', 'B', 'C', 'reject']),
            'status' => fake()->randomElement(['available', 'reserved', 'damaged', 'expired']),
            'notes' => fake()->optional(0.4)->sentence(),
        ];
    }

    /**
     * Food category item
     */
    public function food(): static
    {
        return $this->state([
            'category' => 'food',
            'expiry_date' => fake()->dateTimeBetween('now', '+1 year'),
            'best_before_date' => fake()->dateTimeBetween('now', '+8 months'),
        ]);
    }

    /**
     * Garment category item
     */
    public function garment(): static
    {
        return $this->state([
            'category' => 'garment',
            'expiry_date' => null,
            'best_before_date' => null,
        ]);
    }

    /**
     * Low stock item
     */
    public function lowStock(): static
    {
        return $this->state(function (array $attributes) {
            $minimumStock = $attributes['minimum_stock'] ?? 10;

            return [
                'current_stock' => fake()->numberBetween(0, $minimumStock - 1),
                'reserved_stock' => 0,
            ];
        });
    }

    /**
     * Out of stock item
     */
    public function outOfStock(): static
    {
        return $this->state([
            'current_stock' => 0,
            'reserved_stock' => 0,
        ]);
    }

    /**
     * High stock item
     */
    public function highStock(): static
    {
        return $this->state([
            'current_stock' => fake()->numberBetween(1000, 5000),
            'reserved_stock' => fake()->numberBetween(0, 100),
        ]);
    }

    /**
     * Expiring soon item (food only)
     */
    public function expiringSoon(): static
    {
        return $this->state([
            'category' => 'food',
            'expiry_date' => fake()->dateTimeBetween('now', '+1 week'),
            'status' => 'available',
        ]);
    }

    /**
     * Expired item (food only)
     */
    public function expired(): static
    {
        return $this->state([
            'category' => 'food',
            'expiry_date' => fake()->dateTimeBetween('-3 months', '-1 day'),
            'status' => 'expired',
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
     * Grade C quality
     */
    public function gradeC(): static
    {
        return $this->state([
            'quality_grade' => 'C',
            'unit_cost' => fake()->randomFloat(2, 10, 40),
            'selling_price' => fake()->randomFloat(2, 20, 80),
        ]);
    }

    /**
     * Damaged item
     */
    public function damaged(): static
    {
        return $this->state([
            'status' => 'damaged',
            'notes' => 'Item damaged - '.fake()->sentence(),
            'selling_price' => fake()->randomFloat(2, 1, 20), // Heavily discounted
        ]);
    }

    /**
     * Reserved stock item
     */
    public function reserved(): static
    {
        return $this->state(function (array $attributes) {
            $currentStock = $attributes['current_stock'] ?? 100;

            return [
                'status' => 'reserved',
                'reserved_stock' => fake()->numberBetween(1, $currentStock),
            ];
        });
    }

    /**
     * Frozen storage item (food only)
     */
    public function frozen(): static
    {
        return $this->state([
            'category' => 'food',
            'expiry_date' => fake()->dateTimeBetween('+6 months', '+2 years'),
            'best_before_date' => fake()->dateTimeBetween('+3 months', '+18 months'),
        ]);
    }

    /**
     * Chilled storage item (food only)
     */
    public function chilled(): static
    {
        return $this->state([
            'category' => 'food',
            'expiry_date' => fake()->dateTimeBetween('now', '+6 months'),
            'best_before_date' => fake()->dateTimeBetween('now', '+4 months'),
        ]);
    }
}
