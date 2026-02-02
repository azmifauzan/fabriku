<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PreparationOrder>
 */
class PreparationOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = ['draft', 'in_progress', 'completed'];
        $units = ['pieces', 'kg', 'batch', 'liter'];

        return [
            'tenant_id' => Tenant::factory(),
            'pattern_id' => \App\Models\Pattern::factory(),
            'preparation_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'status' => $this->faker->randomElement($statuses),
            'prepared_by' => \App\Models\User::factory(),
            'output_quantity' => $this->faker->numberBetween(10, 200),
            'material_usage' => [
                [
                    'material_id' => 1,
                    'quantity' => $this->faker->randomFloat(2, 1, 10),
                ],
            ],
            'waste_percentage' => $this->faker->randomFloat(2, 0, 10),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
