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
        $type = fake()->randomElement($types);
        $specialty = fake()->randomElement($specialties);

        return [
            'tenant_id' => Tenant::factory(),
            'code' => strtoupper(fake()->unique()->lexify('CTR-???-###')),
            'name' => $type === 'company' ? fake()->company() : fake()->name(),
            'type' => $type,
            'specialty' => $specialty,
            'contact_person' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->optional(0.7)->safeEmail(),
            'address' => fake()->optional(0.8)->address(),
            'is_active' => true,
            'notes' => fake()->optional(0.3)->sentence(),
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
        return $this->state(fn (array $attributes) => [
            'type' => 'individual',
            'name' => fake()->name(),
        ]);
    }

    public function company(): self
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'company',
            'name' => fake()->company(),
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
