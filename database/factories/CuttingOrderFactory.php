<?php

namespace Database\Factories;

use App\Models\Pattern;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CuttingOrder>
 */
class CuttingOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $orderDate = fake()->dateTimeBetween('-1 month', 'now');
        $targetDate = fake()->optional()->dateTimeBetween($orderDate, '+2 weeks');

        return [
            'tenant_id' => Tenant::factory(),
            'pattern_id' => Pattern::factory(),
            'order_date' => $orderDate,
            'target_date' => $targetDate,
            'target_quantity' => fake()->numberBetween(50, 500),
            'status' => fake()->randomElement(['draft', 'in_progress', 'completed', 'cancelled']),
            'cutter_id' => fake()->optional()->randomElement([null, User::factory()]),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
