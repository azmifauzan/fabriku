<?php

namespace Database\Factories;

use App\Models\Contractor;
use App\Models\CuttingResult;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ProductionOrderFactory extends Factory
{
    public function definition(): array
    {
        $requestedDate = fake()->dateTimeBetween('-1 month', 'now');
        $promisedDate = fake()->optional(0.7)->dateTimeBetween($requestedDate, '+2 weeks');
        $type = fake()->randomElement(['internal', 'external']);
        $quantityRequested = fake()->numberBetween(50, 500);

        return [
            'tenant_id' => Tenant::factory(),
            'cutting_result_id' => CuttingResult::factory(),
            'type' => $type,
            'contractor_id' => $type === 'external' ? Contractor::factory() : null,
            'quantity_requested' => $quantityRequested,
            'quantity_produced' => 0,
            'quantity_good' => 0,
            'quantity_reject' => 0,
            'labor_cost' => fake()->randomFloat(2, 100000, 2000000),
            'requested_date' => $requestedDate,
            'promised_date' => $promisedDate,
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
            'sent_date' => fake()->dateTimeBetween($attributes['requested_date'], 'now'),
        ]);
    }

    public function inProgress(): self
    {
        return $this->state(function (array $attributes) {
            $requestedDate = Carbon::parse($attributes['requested_date'] ?? now()->subWeeks(2));
            $sentDate = fake()->dateTimeBetween($requestedDate, 'now');

            return [
                'status' => 'in_progress',
                'sent_date' => $sentDate,
                'quantity_produced' => fake()->numberBetween(1, $attributes['quantity_requested']),
            ];
        });
    }

    public function completed(): self
    {
        $efficiency = fake()->randomFloat(2, 85, 98); // 85-98% efficiency

        return $this->state(function (array $attributes) use ($efficiency) {
            $requestedDate = Carbon::parse($attributes['requested_date'] ?? now()->subWeeks(3));
            $sentDate = fake()->dateTimeBetween($requestedDate, 'now');
            $completedDate = fake()->dateTimeBetween($sentDate, 'now');

            return [
                'status' => 'completed',
                'sent_date' => $sentDate,
                'completed_date' => $completedDate,
                'quantity_produced' => $attributes['quantity_requested'],
                'quantity_good' => (int) ($attributes['quantity_requested'] * $efficiency / 100),
                'quantity_reject' => fake()->numberBetween(0, (int) ($attributes['quantity_requested'] * 0.05)),
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
