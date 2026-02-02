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
        $quantity = $this->faker->numberBetween(1, 10);
        $unitPrice = $this->faker->randomFloat(2, 50000, 500000);
        $discountAmount = $this->faker->randomElement([0, $unitPrice * 0.05, $unitPrice * 0.1]);
        $subtotal = ($quantity * $unitPrice) - $discountAmount;

        return [
            'sales_order_id' => \App\Models\SalesOrder::factory(),
            'inventory_item_id' => \App\Models\InventoryItem::factory(),
            'product_name' => $this->faker->words(3, true),
            'sku' => strtoupper($this->faker->bothify('SKU-####-???')),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'discount_amount' => $discountAmount,
            'subtotal' => $subtotal,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
