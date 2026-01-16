<?php

namespace Database\Factories;

use App\Models\Material;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaterialReceiptFactory extends Factory
{
    public function definition(): array
    {
        $quantity = fake()->randomFloat(2, 10, 500);
        $pricePerUnit = fake()->randomFloat(2, 10000, 100000);
        $totalCost = $quantity * $pricePerUnit;

        return [
            'tenant_id' => Tenant::factory(),
            'material_id' => Material::factory(),
            'receipt_number' => 'RCP-'.date('Ymd').'-'.fake()->unique()->numberBetween(1000, 9999),
            'supplier_name' => fake()->company(),
            'quantity' => $quantity,
            'unit' => fake()->randomElement(['meter', 'kg', 'pcs', 'roll']),
            'price_per_unit' => $pricePerUnit,
            'total_cost' => $totalCost,
            'receipt_date' => fake()->dateTimeBetween('-30 days', 'now'),
            'batch_number' => fake()->optional()->bothify('BATCH-####'),
            'expired_date' => fake()->optional()->dateTimeBetween('now', '+1 year'),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function forTenant(int $tenantId): self
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => $tenantId,
        ]);
    }
}
