<?php

namespace Database\Factories;

use App\Models\InventoryItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SalesOrderItem>
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
            'sales_order_id' => SalesOrder::factory(),
            'inventory_item_id' => InventoryItem::factory(),
            'product_name' => fake()->words(3, true),
            'sku' => strtoupper(fake()->bothify('SKU-####-???')),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'discount_amount' => $discountAmount,
            'subtotal' => $subtotal,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
