<?php

namespace Database\Factories;

use App\Models\Store;
use App\Models\StoreTheme;
use Illuminate\Database\Eloquent\Factories\Factory;

class StoreThemeFactory extends Factory
{
    protected $model = StoreTheme::class;

    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'primary_color' => fake()->hexColor(),
            'secondary_color' => fake()->hexColor(),
            'button_color' => fake()->hexColor(),
            'text_color' => '#111827',
            'background_color' => '#ffffff',
            'seo_title' => fake()->sentence(),
            'seo_description' => fake()->paragraph(),
        ];
    }
}
