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
            'code' => strtoupper($this->faker->unique()->bothify('PTN-###')),
            'name' => $this->faker->words(3, true),
            'output_quantity' => $this->faker->numberBetween(1, 10),
            'description' => $this->faker->optional()->sentence(),
            'estimated_labor_cost' => $this->faker->randomFloat(2, 5000, 50000),
            'instructions' => $this->faker->optional()->sentence(),
            'is_active' => true,
        ];
    }
}
