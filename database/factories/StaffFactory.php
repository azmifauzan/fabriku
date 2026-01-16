<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Staff>
 */
class StaffFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => \App\Models\Tenant::factory(),
            'code' => fake()->unique()->numerify('STF-###'),
            'name' => fake()->name(),
            'role' => fake()->randomElement(['production', 'quality_control', 'packaging', 'warehouse', 'supervisor']),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'employment_type' => fake()->randomElement(['full_time', 'part_time', 'freelance']),
            'status' => 'active',
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
