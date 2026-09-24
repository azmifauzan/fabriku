<?php

namespace Database\Factories;

use App\Models\BusinessSite;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<BusinessSite>
 */
class BusinessSiteFactory extends Factory
{
    protected $model = BusinessSite::class;

    public function definition(): array
    {
        $name = fake()->company();

        return [
            'tenant_id' => Tenant::factory(),
            'mode' => 'produk',
            'slug' => Str::slug($name).'-'.fake()->unique()->randomNumber(4),
            'custom_domain' => null,
            'cloudflare_hostname_id' => null,
            'domain_status' => 'none',
            'profile' => [
                'name' => $name,
                'description' => fake()->sentence(),
                'whatsapp' => '081234567890',
                'address' => fake()->address(),
                'opening_hours' => 'Senin - Sabtu: 08:00 - 17:00',
            ],
            'seo' => [
                'title' => $name,
                'description' => fake()->sentence(),
            ],
            'status' => 'published',
            'published_at' => now(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn () => [
            'status' => 'suspended',
        ]);
    }

    public function customDomain(string $domain = 'tokokeren.com'): static
    {
        return $this->state(fn () => [
            'custom_domain' => $domain,
            'domain_status' => 'active',
        ]);
    }
}
