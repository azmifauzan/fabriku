<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InventoryLocation>
 */
class InventoryLocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $code = strtoupper(fake()->unique()->lexify('LOC-???-###'));

        return [
            'tenant_id' => Tenant::factory(),
            'code' => $code,
            'name' => fake()->unique()->bothify('Rack ?#-?#'),
            'type' => fake()->randomElement(['rack', 'shelf', 'bin']),
            'capacity' => fake()->optional(0.8)->numberBetween(100, 2000),
            'description' => fake()->optional(0.7)->sentence(),
        ];
    }

    /**
     * Small capacity location
     */
    public function smallCapacity(): static
    {
        return $this->state([
            'capacity' => fake()->numberBetween(50, 200),
        ]);
    }

    /**
     * Large capacity location
     */
    public function largeCapacity(): static
    {
        return $this->state([
            'capacity' => fake()->numberBetween(1000, 5000),
        ]);
    }

    /**
     * No capacity limit
     */
    public function unlimitedCapacity(): static
    {
        return $this->state([
            'capacity' => null,
        ]);
    }
}
