<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['retail', 'reseller', 'online'];
        $paymentTerms = ['cash', 'credit_7', 'credit_14', 'credit_30'];

        return [
            'tenant_id' => 1,
            'code' => fake()->unique()->numerify('CUST-####'),
            'name' => fake()->company(),
            'type' => fake()->randomElement($types),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->optional()->safeEmail(),
            'address' => fake()->optional()->address(),
            'city' => fake()->optional()->city(),
            'province' => fake()->optional()->state(),
            'discount_percentage' => fake()->randomElement([0, 5, 10, 15]),
            'payment_term' => fake()->randomElement($paymentTerms),
            'notes' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
