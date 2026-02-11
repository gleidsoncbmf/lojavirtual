<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductVariationFactory extends Factory
{
    protected $model = ProductVariation::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'name' => fake()->randomElement(['P', 'M', 'G', 'GG', '38', '39', '40', '41', '42']),
            'price' => null,
            'stock' => fake()->numberBetween(1, 20),
            'sku' => fake()->unique()->bothify('VAR-####'),
        ];
    }
}
