<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreTheme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminProductTest extends TestCase
{
    use RefreshDatabase;

    private Store $store;
    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->store = Store::factory()->create();
        StoreTheme::factory()->create(['store_id' => $this->store->id]);
        $this->owner = User::factory()->create([
            'store_id' => $this->store->id,
            'role' => 'store_owner',
        ]);
    }

    public function test_store_owner_can_list_products(): void
    {
        $category = Category::factory()->create(['store_id' => $this->store->id]);
        Product::factory()->count(3)->create([
            'store_id' => $this->store->id,
            'category_id' => $category->id,
        ]);

        Sanctum::actingAs($this->owner);

        $response = $this->getJson('/api/admin/products');

        $response->assertOk();
    }

    public function test_store_owner_can_create_product(): void
    {
        $category = Category::factory()->create(['store_id' => $this->store->id]);

        Sanctum::actingAs($this->owner);

        $response = $this->postJson('/api/admin/products', [
            'name' => 'New Product',
            'slug' => 'new-product',
            'description' => 'A great product.',
            'price' => 59.90,
            'sku' => 'NP-001',
            'stock' => 25,
            'category_id' => $category->id,
            'active' => true,
            'images' => ['https://example.com/img.jpg'],
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('products', ['slug' => 'new-product', 'store_id' => $this->store->id]);
    }

    public function test_store_owner_can_update_product(): void
    {
        $category = Category::factory()->create(['store_id' => $this->store->id]);
        $product = Product::factory()->create([
            'store_id' => $this->store->id,
            'category_id' => $category->id,
        ]);

        Sanctum::actingAs($this->owner);

        $response = $this->putJson("/api/admin/products/{$product->id}", [
            'name' => 'Updated Name',
            'price' => 99.90,
        ]);

        $response->assertOk();
    }

    public function test_store_owner_can_delete_product(): void
    {
        $category = Category::factory()->create(['store_id' => $this->store->id]);
        $product = Product::factory()->create([
            'store_id' => $this->store->id,
            'category_id' => $category->id,
        ]);

        Sanctum::actingAs($this->owner);

        $response = $this->deleteJson("/api/admin/products/{$product->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_unauthenticated_user_cannot_access_admin(): void
    {
        $response = $this->getJson('/api/admin/products');

        $response->assertStatus(401);
    }

    public function test_customer_cannot_access_admin(): void
    {
        $customer = User::factory()->create([
            'store_id' => $this->store->id,
            'role' => 'customer',
        ]);

        Sanctum::actingAs($customer);

        $response = $this->getJson('/api/admin/products');

        $response->assertStatus(403);
    }

    public function test_platform_admin_can_access_admin_routes(): void
    {
        // Platform admin needs a store to list products
        $admin = User::factory()->create([
            'store_id' => $this->store->id,
            'role' => 'platform_admin',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/admin/products');

        $response->assertOk();
    }
}
