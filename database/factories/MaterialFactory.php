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
            'code' => strtoupper($this->faker->unique()->lexify('MAT-???-###')),
            'name' => $this->faker->words(3, true),
            'supplier_name' => $this->faker->optional()->company(),
            'price_per_unit' => $this->faker->randomFloat(2, 10000, 500000),
            'stock_quantity' => 0,
            'min_stock' => $this->faker->randomFloat(2, 10, 100),
            'unit' => $this->faker->randomElement($units),
            'description' => $this->faker->optional()->sentence(),
        ];
    }

    public function forTenant(int $tenantId): self
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => $tenantId,
        ]);
    }
}
