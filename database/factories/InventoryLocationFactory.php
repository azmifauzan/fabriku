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
        $code = strtoupper($this->faker->unique()->lexify('LOC-???-###'));

        return [
            'tenant_id' => Tenant::factory(),
            'code' => $code,
            'name' => $this->faker->unique()->bothify('Rack ?#-?#'),
            'capacity' => $this->faker->optional(0.8)->numberBetween(100, 2000),
            'is_active' => true,
        ];
    }

    /**
     * Small capacity location
     */
    public function smallCapacity(): static
    {
        return $this->state([
            'capacity' => $this->faker->numberBetween(50, 200),
        ]);
    }

    /**
     * Large capacity location
     */
    public function largeCapacity(): static
    {
        return $this->state([
            'capacity' => $this->faker->numberBetween(1000, 5000),
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
