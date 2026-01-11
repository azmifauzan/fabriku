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
        $statuses = ['draft', 'confirmed', 'processing', 'shipped', 'completed'];
        $paymentMethods = ['cash', 'transfer', 'credit_card', 'qris', 'cod'];
        $paymentStatuses = ['unpaid', 'partial', 'paid'];

        $subtotal = fake()->randomFloat(2, 100000, 5000000);
        $discountPercentage = fake()->randomElement([0, 5, 10, 15]);
        $discountAmount = $subtotal * ($discountPercentage / 100);
        $taxAmount = 0;
        $totalAmount = $subtotal - $discountAmount + $taxAmount;

        return [
            'tenant_id' => 1,
            'customer_id' => \App\Models\Customer::factory(),
            'order_date' => fake()->dateTimeBetween('-3 months', 'now'),
            'channel' => fake()->randomElement($channels),
            'status' => fake()->randomElement($statuses),
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'discount_percentage' => $discountPercentage,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'payment_method' => fake()->randomElement($paymentMethods),
            'payment_status' => fake()->randomElement($paymentStatuses),
            'paid_amount' => fake()->randomElement([0, $totalAmount / 2, $totalAmount]),
            'payment_due_date' => fake()->optional()->dateTimeBetween('now', '+30 days'),
            'shipped_date' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'completed_date' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'notes' => fake()->optional()->sentence(),
            'shipping_address' => fake()->optional()->address(),
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
