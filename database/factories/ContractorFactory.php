<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContractorFactory extends Factory
{
    public function definition(): array
    {
        $types = ['individual', 'company'];
        $specialties = [
            'Penjahit mukena dan gamis',
            'Kue tradisional dan modern',
            'Furniture dan kerajinan kayu',
            'Bordir dan sablon',
            'Produksi garment massal',
        ];
        $type = $this->faker->randomElement($types);
        $specialty = $this->faker->randomElement($specialties);

        return [
            'tenant_id' => Tenant::factory(),
            'code' => strtoupper($this->faker->unique()->lexify('CTR-???-###')),
            'name' => $type === 'company' ? $this->faker->company() : $this->faker->name(),
            'type' => $type,
            'specialty' => $specialty,
            'contact_person' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->optional(0.7)->safeEmail(),
            'address' => $this->faker->optional(0.8)->address(),
            'is_active' => true,
            'notes' => $this->faker->optional(0.3)->sentence(),
        ];
    }

    public function forTenant(int $tenantId): self
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => $tenantId,
        ]);
    }

    public function individual(): self
    {
        $faker = \Faker\Factory::create();

        return $this->state(fn (array $attributes) => [
            'type' => 'individual',
            'name' => $faker->name(),
        ]);
    }

    public function company(): self
    {
        $faker = \Faker\Factory::create();

        return $this->state(fn (array $attributes) => [
            'type' => 'company',
            'name' => $faker->company(),
        ]);
    }

    public function sewing(): self
    {
        return $this->state(fn (array $attributes) => [
            'specialty' => 'Penjahit mukena dan gamis',
        ]);
    }

    public function baking(): self
    {
        return $this->state(fn (array $attributes) => [
            'specialty' => 'Kue tradisional dan modern',
        ]);
    }

    public function inactive(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
