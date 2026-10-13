<?php

namespace Database\Factories;

use App\Models\InventoryItem;
use App\Models\SiteProduct;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SiteProduct>
 */
class SiteProductFactory extends Factory
{
    protected $model = SiteProduct::class;

    public function definition(): array
    {
        $title = fake()->words(3, true);

        return [
            'tenant_id' => Tenant::factory(),
            'inventory_item_id' => fn (array $attributes) => InventoryItem::factory()->create(['tenant_id' => $attributes['tenant_id']])->id,
            'slug' => Str::slug($title).'-'.fake()->unique()->randomNumber(4),
            'title' => ucfirst($title),
            'description' => fake()->paragraph(),
            'image_path' => null,
            'sort' => 0,
            'is_visible' => true,
        ];
    }
}
