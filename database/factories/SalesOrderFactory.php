<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SalesOrder>
 */
class SalesOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $channels = ['offline', 'online', 'reseller', 'marketplace'];

        $subtotal = fake()->randomFloat(2, 100000, 5000000);
        $discountAmount = fake()->randomFloat(2, 0, $subtotal * 0.2);
        $taxAmount = 0;
        $shippingCost = fake()->randomFloat(2, 0, 50000);
        $totalAmount = $subtotal - $discountAmount + $taxAmount + $shippingCost;

        return [
            'tenant_id' => 1,
            'order_number' => strtoupper(fake()->unique()->bothify('SO-######')),
            'customer_id' => \App\Models\Customer::factory(),
            'order_date' => fake()->dateTimeBetween('-3 months', 'now'),
            'delivery_date' => fake()->optional()->dateTimeBetween('now', '+30 days'),
            'channel' => fake()->randomElement($channels),
            'status' => fake()->randomElement(['draft', 'confirmed', 'completed']),
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'discount_percentage' => 0,
            'tax_amount' => $taxAmount,
            'shipping_cost' => fake()->randomFloat(2, 0, 50000),
            'total_amount' => $totalAmount,
            'payment_method' => fake()->randomElement(['cash', 'transfer', 'credit']),
            'payment_status' => fake()->randomElement(['unpaid', 'pending', 'partial', 'paid']),
            'paid_amount' => fake()->randomElement([0, $totalAmount / 2, $totalAmount]),
            'shipping_address' => fake()->optional()->address(),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'payment_status' => 'unpaid',
            'paid_amount' => 0,
        ]);
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
            'payment_status' => 'pending',
            'paid_amount' => 0,
        ]);
    }

    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'processing',
            'payment_status' => 'pending',
            'paid_amount' => 0,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'payment_status' => 'paid',
            'paid_amount' => $attributes['total_amount'],
            'completed_date' => now(),
        ]);
    }
}
