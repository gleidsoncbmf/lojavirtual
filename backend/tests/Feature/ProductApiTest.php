<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreDomain;
use App\Models\StoreTheme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();

        $this->store = Store::factory()->create();
        StoreTheme::factory()->create(['store_id' => $this->store->id]);
    }

    private function storeHeaders(): array
    {
        return ['X-Store-Id' => $this->store->id];
    }

    public function test_can_list_active_products(): void
    {
        $category = Category::factory()->create(['store_id' => $this->store->id]);
        Product::factory()->count(3)->create([
            'store_id' => $this->store->id,
            'category_id' => $category->id,
        ]);
        // Inactive product should not appear
        Product::factory()->inactive()->create([
            'store_id' => $this->store->id,
            'category_id' => $category->id,
        ]);

        $response = $this->getJson('/api/products', $this->storeHeaders());

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_can_get_product_by_slug(): void
    {
        $category = Category::factory()->create(['store_id' => $this->store->id]);
        $product = Product::factory()->create([
            'store_id' => $this->store->id,
            'category_id' => $category->id,
            'slug' => 'test-product',
        ]);

        $response = $this->getJson('/api/products/test-product', $this->storeHeaders());

        $response->assertOk()
            ->assertJsonPath('data.name', $product->name);
    }

    public function test_returns_404_for_inactive_product(): void
    {
        $category = Category::factory()->create(['store_id' => $this->store->id]);
        Product::factory()->inactive()->create([
            'store_id' => $this->store->id,
            'category_id' => $category->id,
            'slug' => 'hidden-product',
        ]);

        $response = $this->getJson('/api/products/hidden-product', $this->storeHeaders());

        $response->assertStatus(404);
    }

    public function test_returns_404_for_nonexistent_product(): void
    {
        $response = $this->getJson('/api/products/does-not-exist', $this->storeHeaders());

        $response->assertStatus(404);
    }

    public function test_can_list_categories(): void
    {
        Category::factory()->count(3)->create([
            'store_id' => $this->store->id,
        ]);

        $response = $this->getJson('/api/categories', $this->storeHeaders());

        $response->assertOk();
    }

    public function test_products_from_another_store_not_visible(): void
    {
        $otherStore = Store::factory()->create();
        $category = Category::factory()->create(['store_id' => $otherStore->id]);
        Product::factory()->count(2)->create([
            'store_id' => $otherStore->id,
            'category_id' => $category->id,
        ]);

        $response = $this->getJson('/api/products', $this->storeHeaders());

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_store_resolution_fails_without_header(): void
    {
        $response = $this->getJson('/api/products');

        $response->assertStatus(404)
            ->assertJson(['message' => 'Loja não encontrada ou inativa.']);
    }

    public function test_inactive_store_is_rejected(): void
    {
        $inactiveStore = Store::factory()->inactive()->create();

        $response = $this->getJson('/api/products', ['X-Store-Id' => $inactiveStore->id]);

        $response->assertStatus(404);
    }
}
