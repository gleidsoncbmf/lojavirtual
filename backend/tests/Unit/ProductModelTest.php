<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_has_stock_returns_true_when_stock_positive(): void
    {
        $store = Store::factory()->create();
        $category = Category::factory()->create(['store_id' => $store->id]);
        $product = Product::factory()->create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'stock' => 10,
        ]);

        $this->assertTrue($product->hasStock());
    }

    public function test_product_has_stock_returns_false_when_stock_zero(): void
    {
        $store = Store::factory()->create();
        $category = Category::factory()->create(['store_id' => $store->id]);
        $product = Product::factory()->outOfStock()->create([
            'store_id' => $store->id,
            'category_id' => $category->id,
        ]);

        $this->assertFalse($product->hasStock());
    }

    public function test_decrement_stock_reduces_stock(): void
    {
        $store = Store::factory()->create();
        $category = Category::factory()->create(['store_id' => $store->id]);
        $product = Product::factory()->create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'stock' => 10,
        ]);

        $product->decrementStock(3);
        $product->refresh();

        $this->assertEquals(7, $product->stock);
    }

    public function test_product_belongs_to_store(): void
    {
        $store = Store::factory()->create();
        $category = Category::factory()->create(['store_id' => $store->id]);
        $product = Product::factory()->create([
            'store_id' => $store->id,
            'category_id' => $category->id,
        ]);

        $this->assertEquals($store->id, $product->store->id);
    }

    public function test_product_belongs_to_category(): void
    {
        $store = Store::factory()->create();
        $category = Category::factory()->create(['store_id' => $store->id]);
        $product = Product::factory()->create([
            'store_id' => $store->id,
            'category_id' => $category->id,
        ]);

        $this->assertEquals($category->id, $product->category->id);
    }

    public function test_product_has_variations(): void
    {
        $store = Store::factory()->create();
        $category = Category::factory()->create(['store_id' => $store->id]);
        $product = Product::factory()->create([
            'store_id' => $store->id,
            'category_id' => $category->id,
        ]);

        ProductVariation::factory()->count(3)->create(['product_id' => $product->id]);

        $this->assertCount(3, $product->variations);
    }

    public function test_images_casts_to_array(): void
    {
        $store = Store::factory()->create();
        $category = Category::factory()->create(['store_id' => $store->id]);
        $product = Product::factory()->create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'images' => ['img1.jpg', 'img2.jpg'],
        ]);

        $this->assertIsArray($product->images);
        $this->assertCount(2, $product->images);
    }
}
