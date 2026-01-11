<?php

namespace Database\Factories;

use App\Models\ProductionOrder;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductionBatchFactory extends Factory
{
    public function definition(): array
    {
        $received = fake()->numberBetween(50, 200);
        $qualityRate = fake()->randomFloat(2, 85, 98); // 85-98% quality rate
        $good = (int) ($received * $qualityRate / 100);
        $defect = fake()->numberBetween(0, (int) ($received * 0.1));
        $reject = $received - $good - $defect;

        $grades = ['A', 'B', 'C', 'reject'];
        $grade = fake()->randomElement($grades);

        $productionDate = fake()->dateTimeBetween('-2 weeks', '-1 day');
        $receivedDate = fake()->dateTimeBetween($productionDate, 'now');

        // For food products, add expiry date
        $expiryDate = fake()->optional(0.3)->dateTimeBetween($receivedDate, '+30 days');

        return [
            'tenant_id' => Tenant::factory(),
            'production_order_id' => ProductionOrder::factory(),
            'quantity_received' => $received,
            'quantity_good' => $good,
            'quantity_defect' => max(0, $defect),
            'quantity_reject' => max(0, $reject),
            'grade' => $grade,
            'labor_cost_actual' => fake()->optional(0.7)->randomFloat(2, 50000, 500000),
            'production_cost' => fake()->optional(0.8)->randomFloat(2, 100000, 1000000),
            'production_date' => $productionDate,
            'received_date' => $receivedDate,
            'expiry_date' => $expiryDate,
            'qc_notes' => fake()->optional(0.4)->sentence(),
            'defect_reasons' => fake()->optional(0.3)->sentence(),
            'qc_checklist' => fake()->optional(0.2)->randomElements([
                'color_check' => fake()->boolean(),
                'size_check' => fake()->boolean(),
                'stitching_quality' => fake()->boolean(),
                'packaging_check' => fake()->boolean(),
            ]),
        ];
    }

    public function forTenant(int $tenantId): self
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => $tenantId,
        ]);
    }

    public function gradeA(): self
    {
        return $this->state(fn (array $attributes) => [
            'grade' => 'A',
            'quantity_good' => (int) ($attributes['quantity_received'] * 0.98),
            'quantity_defect' => (int) ($attributes['quantity_received'] * 0.02),
            'quantity_reject' => 0,
        ]);
    }

    public function gradeB(): self
    {
        return $this->state(fn (array $attributes) => [
            'grade' => 'B',
            'quantity_good' => (int) ($attributes['quantity_received'] * 0.92),
            'quantity_defect' => (int) ($attributes['quantity_received'] * 0.06),
            'quantity_reject' => (int) ($attributes['quantity_received'] * 0.02),
        ]);
    }

    public function withExpiry(int $days = 30): self
    {
        return $this->state(fn (array $attributes) => [
            'expiry_date' => fake()->dateTimeBetween($attributes['received_date'], "+{$days} days"),
        ]);
    }

    public function reject(): self
    {
        return $this->state(fn (array $attributes) => [
            'grade' => 'reject',
            'quantity_good' => 0,
            'quantity_defect' => 0,
            'quantity_reject' => $attributes['quantity_received'],
            'defect_reasons' => 'Quality tidak memenuhi standar',
        ]);
    }
}
