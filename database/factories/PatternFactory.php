<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pattern>
 */
class PatternFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'code' => strtoupper(fake()->unique()->bothify('PTN-###')),
            'name' => fake()->words(3, true),
            'output_quantity' => fake()->numberBetween(1, 10),
            'description' => fake()->optional()->sentence(),
            'estimated_labor_cost' => fake()->randomFloat(2, 5000, 50000),
            'instructions' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }
}
