<?php

namespace Database\Factories;

use App\Models\Material;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaterialReceiptFactory extends Factory
{
    public function definition(): array
    {
        $quantity = fake()->randomFloat(2, 10, 500);
        $unitPrice = fake()->randomFloat(2, 10000, 100000);

        return [
            'tenant_id' => Tenant::factory(),
            'receipt_number' => 'RCP-'.date('Ymd').'-'.fake()->unique()->numberBetween(1000, 9999),
            'material_id' => Material::factory(),
            'supplier_name' => fake()->company(),
            'receipt_date' => fake()->dateTimeBetween('-30 days', 'now'),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $quantity * $unitPrice,
            'rolls_count' => fake()->optional()->numberBetween(1, 20),
            'length_per_roll' => fake()->optional()->randomFloat(2, 10, 100),
            'batch_number' => fake()->optional()->bothify('BATCH-####'),
            'notes' => fake()->optional()->sentence(),
            'received_by' => User::factory(),
            'attachments' => [],
        ];
    }

    public function forTenant(int $tenantId): self
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => $tenantId,
        ]);
    }
}
