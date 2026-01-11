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
        $zones = ['A', 'B', 'C', 'D', 'E'];
        $zone = fake()->randomElement($zones);
        $rack = fake()->numberBetween(1, 10);

        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->unique()->bothify('Warehouse ?#-?#'),
            'zone' => $zone,
            'rack' => sprintf('%s-%02d', $zone, $rack),
            'description' => fake()->optional(0.7)->sentence(),
            'capacity' => fake()->optional(0.8)->numberBetween(100, 2000),
            'temperature_min' => fake()->optional(0.3)->numberBetween(-10, 5),
            'temperature_max' => fake()->optional(0.3)->numberBetween(15, 30),
            'status' => fake()->randomElement(['active', 'inactive', 'maintenance']),
            'notes' => fake()->optional(0.4)->sentence(),
        ];
    }

    /**
     * Indicate that the location is a freezer
     */
    public function freezer(): static
    {
        return $this->state([
            'temperature_min' => fake()->numberBetween(-25, -18),
            'temperature_max' => fake()->numberBetween(-15, -10),
            'name' => fake()->unique()->bothify('Freezer ?#-?#'),
        ]);
    }

    /**
     * Indicate that the location is a chiller
     */
    public function chiller(): static
    {
        return $this->state([
            'temperature_min' => fake()->numberBetween(2, 4),
            'temperature_max' => fake()->numberBetween(6, 8),
            'name' => fake()->unique()->bothify('Chiller ?#-?#'),
        ]);
    }

    /**
     * Indicate that the location is room temperature
     */
    public function roomTemp(): static
    {
        return $this->state([
            'temperature_min' => null,
            'temperature_max' => null,
            'name' => fake()->unique()->bothify('Room ?#-?#'),
        ]);
    }

    /**
     * Indicate that the location is inactive
     */
    public function inactive(): static
    {
        return $this->state([
            'status' => 'inactive',
        ]);
    }

    /**
     * Indicate that the location is under maintenance
     */
    public function maintenance(): static
    {
        return $this->state([
            'status' => 'maintenance',
            'notes' => 'Under maintenance - '.fake()->sentence(),
        ]);
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
