<?php

namespace Database\Factories;

use App\Models\Pattern;
use App\Models\PreparationOrder;
use App\Models\Staff;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PreparationOrder>
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
            'pattern_id' => Pattern::factory(),
            'preparation_date' => fake()->dateTimeBetween('-30 days', 'now'),
            'status' => fake()->randomElement($statuses),
            'prepared_by' => Staff::factory(),
            'output_quantity' => fake()->numberBetween(10, 200),
            'material_usage' => [
                [
                    'material_id' => 1,
                    'quantity' => fake()->randomFloat(2, 1, 10),
                ],
            ],
            'waste_percentage' => fake()->randomFloat(2, 0, 10),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
