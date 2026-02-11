<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'category_id' => Category::factory(),
            'name' => fake()->words(3, true),
            'slug' => fake()->unique()->slug(3),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 10, 500),
            'compare_price' => null,
            'sku' => fake()->unique()->bothify('SKU-####'),
            'stock' => fake()->numberBetween(1, 100),
            'active' => true,
            'images' => ['https://placehold.co/600x800/333/fff?text=Product'],
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn() => ['active' => false]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn() => ['stock' => 0]);
    }
}
