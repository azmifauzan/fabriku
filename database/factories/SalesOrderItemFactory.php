<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SalesOrderItem>
 */
class SalesOrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 10);
        $unitPrice = fake()->randomFloat(2, 50000, 500000);
        $discountAmount = fake()->randomElement([0, $unitPrice * 0.05, $unitPrice * 0.1]);
        $subtotal = ($quantity * $unitPrice) - $discountAmount;

        return [
            'sales_order_id' => \App\Models\SalesOrder::factory(),
            'inventory_item_id' => \App\Models\InventoryItem::factory(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'discount_amount' => $discountAmount,
            'subtotal' => $subtotal,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
