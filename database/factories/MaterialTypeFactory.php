<?php

namespace Database\Factories;

use App\Models\MaterialType;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MaterialType>
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
            'tenant_id' => Tenant::factory(),
            'name' => $type['name'],
            'code' => strtoupper(fake()->unique()->lexify($type['code'].'-???')),
            'unit' => $type['unit'],
            'description' => fake()->optional()->sentence(),
        ];
    }
}
