<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreDomain;
use App\Models\StoreTheme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_has_theme(): void
    {
        $store = Store::factory()->create();
        StoreTheme::factory()->create(['store_id' => $store->id]);

        $this->assertNotNull($store->theme);
        $this->assertInstanceOf(StoreTheme::class, $store->theme);
    }

    public function test_store_has_domains(): void
    {
        $store = Store::factory()->create();
        StoreDomain::factory()->count(2)->create(['store_id' => $store->id]);

        $this->assertCount(2, $store->domains);
    }

    public function test_store_has_users(): void
    {
        $store = Store::factory()->create();
        User::factory()->count(3)->create(['store_id' => $store->id]);

        $this->assertCount(3, $store->users);
    }

    public function test_store_has_products(): void
    {
        $store = Store::factory()->create();
        $category = Category::factory()->create(['store_id' => $store->id]);
        Product::factory()->count(5)->create([
            'store_id' => $store->id,
            'category_id' => $category->id,
        ]);

        $this->assertCount(5, $store->products);
    }

    public function test_store_primary_domain(): void
    {
        $store = Store::factory()->create();
        StoreDomain::factory()->create([
            'store_id' => $store->id,
            'is_primary' => true,
            'domain' => 'primary.test',
        ]);
        StoreDomain::factory()->create([
            'store_id' => $store->id,
            'is_primary' => false,
            'domain' => 'secondary.test',
        ]);

        $this->assertEquals('primary.test', $store->primaryDomain->domain);
    }

    public function test_store_owner(): void
    {
        $store = Store::factory()->create();
        $owner = User::factory()->create([
            'store_id' => $store->id,
            'role' => 'store_owner',
        ]);
        User::factory()->create([
            'store_id' => $store->id,
            'role' => 'customer',
        ]);

        $this->assertEquals($owner->id, $store->owner->id);
    }

    public function test_payment_config_casts_to_array(): void
    {
        $store = Store::factory()->create([
            'payment_config' => ['stripe' => ['enabled' => true]],
        ]);

        $this->assertIsArray($store->payment_config);
        $this->assertTrue($store->payment_config['stripe']['enabled']);
    }
}
