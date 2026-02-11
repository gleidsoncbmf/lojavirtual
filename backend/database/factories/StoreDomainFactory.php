<?php

namespace Database\Factories;

use App\Models\Store;
use App\Models\StoreDomain;
use Illuminate\Database\Eloquent\Factories\Factory;

class StoreDomainFactory extends Factory
{
    protected $model = StoreDomain::class;

    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'domain' => fake()->unique()->domainName(),
            'is_primary' => true,
            'verified' => true,
        ];
    }
}
