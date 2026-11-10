<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'created_by' => User::factory()->admin(),
            'name' => rtrim(fake()->unique()->sentence(3), '.'),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 100, 50000),
            'thumbnail_path' => null,
        ];
    }
}
