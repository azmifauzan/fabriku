<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MaterialType>
 */
class MaterialTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = [
            ['name' => 'Kain', 'code' => 'KAIN', 'unit' => 'meter'],
            ['name' => 'Benang', 'code' => 'BENANG', 'unit' => 'roll'],
            ['name' => 'Aksesoris', 'code' => 'AKSESORIS', 'unit' => 'pcs'],
            ['name' => 'Tepung', 'code' => 'TEPUNG', 'unit' => 'kg'],
            ['name' => 'Gula', 'code' => 'GULA', 'unit' => 'kg'],
            ['name' => 'Mentega', 'code' => 'MENTEGA', 'unit' => 'kg'],
        ];

        $type = fake()->randomElement($types);

        return [
            'tenant_id' => \App\Models\Tenant::factory(),
            'name' => $type['name'],
            'code' => strtoupper(fake()->unique()->lexify($type['code'].'-???')),
            'unit' => $type['unit'],
            'description' => fake()->optional()->sentence(),
        ];
    }
}
