<?php

namespace Database\Factories;

use App\Models\Contractor;
use App\Models\PreparationOrder;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductionOrderFactory extends Factory
{
    public function definition(): array
    {
        $estimatedCompletion = fake()->optional(0.7)->dateTimeBetween('now', '+2 weeks');
        $type = fake()->randomElement(['internal', 'external']);

        return [
            'tenant_id' => Tenant::factory(),
            'preparation_order_id' => PreparationOrder::factory(),
            'type' => $type,
            'contractor_id' => $type === 'external' ? Contractor::factory() : null,
            'quantity_produced' => 0,
            'quantity_good' => 0,
            'quantity_reject' => 0,
            'labor_cost' => fake()->randomFloat(2, 100000, 2000000),
            'estimated_completion_date' => $estimatedCompletion,
            'sent_date' => null,
            'completed_date' => null,
            'status' => 'draft',
            'priority' => fake()->randomElement(['low', 'normal', 'high', 'urgent']),
            'notes' => fake()->optional(0.4)->sentence(),
            'completion_notes' => null,
        ];
    }

    public function forTenant(int $tenantId): self
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => $tenantId,
        ]);
    }

    public function internal(): self
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'internal',
            'contractor_id' => null,
        ]);
    }

    public function external(): self
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'external',
            'contractor_id' => Contractor::factory(),
        ]);
    }

    public function sent(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'sent',
            'sent_date' => fake()->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    public function inProgress(): self
    {
        return $this->state(function (array $attributes) {
            $sentDate = fake()->dateTimeBetween('-2 weeks', 'now');

            return [
                'status' => 'in_progress',
                'sent_date' => $sentDate,
                'quantity_produced' => fake()->numberBetween(50, 300),
            ];
        });
    }

    public function completed(): self
    {
        $efficiency = fake()->randomFloat(2, 85, 98); // 85-98% efficiency

        return $this->state(function (array $attributes) use ($efficiency) {
            $sentDate = fake()->dateTimeBetween('-3 weeks', '-1 week');
            $completedDate = fake()->dateTimeBetween($sentDate, 'now');
            $quantityProduced = fake()->numberBetween(50, 500);

            return [
                'status' => 'completed',
                'sent_date' => $sentDate,
                'completed_date' => $completedDate,
                'quantity_produced' => $quantityProduced,
                'quantity_good' => (int) ($quantityProduced * $efficiency / 100),
                'quantity_reject' => fake()->numberBetween(0, (int) ($quantityProduced * 0.05)),
                'completion_notes' => fake()->optional(0.6)->sentence(),
            ];
        });
    }

    public function cancelled(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }
}
