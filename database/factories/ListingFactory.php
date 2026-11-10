<?php

namespace Database\Factories;

use App\Models\Listing;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Listing>
 */
class ListingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'shop_id' => Shop::factory(),
            'product_id' => Product::factory(),
            'price' => fake()->randomFloat(2, 100, 50000),
            'description' => null,
            'thumbnail_path' => null,
        ];
    }
}
