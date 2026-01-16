<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaterialFactory extends Factory
{
    public function definition(): array
    {
        $units = ['meter', 'roll', 'pcs', 'kg'];

        return [
            'tenant_id' => Tenant::factory(),
            'material_type_id' => \App\Models\MaterialType::factory(),
            'code' => strtoupper(fake()->unique()->lexify('MAT-???-###')),
            'name' => fake()->words(3, true),
            'supplier_name' => fake()->optional()->company(),
            'price_per_unit' => fake()->randomFloat(2, 10000, 500000),
            'stock_quantity' => 0,
            'min_stock' => fake()->randomFloat(2, 10, 100),
            'unit' => fake()->randomElement($units),
            'description' => fake()->optional()->sentence(),
        ];
    }

    public function forTenant(int $tenantId): self
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => $tenantId,
        ]);
    }
}
