<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaterialFactory extends Factory
{
    public function definition(): array
    {
        $types = ['kain', 'benang', 'aksesoris', 'kancing', 'resleting'];
        $units = ['meter', 'roll', 'pcs', 'kg'];

        return [
            'tenant_id' => Tenant::factory(),
            'code' => strtoupper(fake()->unique()->lexify('MAT-???-###')),
            'name' => fake()->words(3, true),
            'type' => fake()->randomElement($types),
            'description' => fake()->optional()->sentence(),
            'unit' => fake()->randomElement($units),
            'standard_price' => fake()->randomFloat(2, 10000, 500000),
            'current_stock' => 0,
            'reorder_point' => fake()->randomFloat(2, 10, 100),
            'is_active' => true,
        ];
    }

    public function forTenant(int $tenantId): self
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => $tenantId,
        ]);
    }

    public function inactive(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
