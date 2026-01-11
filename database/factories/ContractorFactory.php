<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContractorFactory extends Factory
{
    public function definition(): array
    {
        $types = ['individual', 'company'];
        $specialties = ['sewing', 'baking', 'crafting', 'other'];
        $type = fake()->randomElement($types);
        $specialty = fake()->randomElement($specialties);

        return [
            'tenant_id' => Tenant::factory(),
            'name' => $type === 'company' ? fake()->company() : fake()->name(),
            'contact_person' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->optional(0.7)->safeEmail(),
            'address' => fake()->optional(0.8)->address(),
            'type' => $type,
            'specialty' => $specialty,
            'rate_per_piece' => fake()->optional(0.6)->randomFloat(2, 1000, 50000),
            'rate_per_hour' => fake()->optional(0.4)->randomFloat(2, 15000, 100000),
            'status' => 'active',
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
            'specialty' => 'sewing',
        ]);
    }

    public function baking(): self
    {
        return $this->state(fn (array $attributes) => [
            'specialty' => 'baking',
        ]);
    }

    public function inactive(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}
