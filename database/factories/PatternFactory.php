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
        $productTypes = ['mukena', 'daster', 'gamis', 'jilbab', 'lainnya'];
        $sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'all_size'];

        return [
            'tenant_id' => Tenant::factory(),
            'code' => strtoupper(fake()->unique()->bothify('???-###')),
            'name' => fake()->words(3, true),
            'product_type' => fake()->randomElement($productTypes),
            'size' => fake()->optional()->randomElement($sizes),
            'description' => fake()->optional()->sentence(),
            'estimated_time' => fake()->numberBetween(20, 120),
            'standard_waste_percentage' => fake()->randomFloat(2, 3, 10),
            'image_url' => fake()->optional()->imageUrl(),
            'is_active' => fake()->boolean(80),
        ];
    }
}
