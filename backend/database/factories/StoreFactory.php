<?php

namespace Database\Factories;

use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

class StoreFactory extends Factory
{
    protected $model = Store::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'slug' => fake()->unique()->slug(2),
            'email' => fake()->companyEmail(),
            'whatsapp' => fake()->numerify('55119########'),
            'status' => 'active',
            'payment_config' => [
                'stripe' => ['enabled' => false, 'public_key' => '', 'secret_key' => ''],
                'mercadopago' => ['enabled' => false, 'access_token' => '', 'public_key' => ''],
            ],
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn() => ['status' => 'inactive']);
    }
}
