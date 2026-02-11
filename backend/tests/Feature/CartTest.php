<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreTheme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    private Store $store;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->store = Store::factory()->create();
        StoreTheme::factory()->create(['store_id' => $this->store->id]);
        $category = Category::factory()->create(['store_id' => $this->store->id]);
        $this->product = Product::factory()->create([
            'store_id' => $this->store->id,
            'category_id' => $category->id,
            'price' => 99.90,
            'stock' => 10,
        ]);
    }

    private function storeHeaders(array $extra = []): array
    {
        return array_merge(['X-Store-Id' => $this->store->id], $extra);
    }

    public function test_can_get_empty_cart(): void
    {
        $response = $this->getJson('/api/cart', $this->storeHeaders([
            'X-Cart-Session' => 'test-session-123',
        ]));

        $response->assertOk()
            ->assertJsonPath('data.session_id', 'test-session-123');
    }

    public function test_can_add_item_to_cart(): void
    {
        $response = $this->postJson('/api/cart/items', [
            'product_id' => $this->product->id,
            'quantity' => 2,
            'session_id' => 'cart-session-1',
        ], $this->storeHeaders());

        $response->assertStatus(201)
            ->assertJson(['message' => 'Item adicionado ao carrinho.']);
    }

    public function test_cannot_add_product_from_another_store(): void
    {
        $otherStore = Store::factory()->create();
        $otherCategory = Category::factory()->create(['store_id' => $otherStore->id]);
        $otherProduct = Product::factory()->create([
            'store_id' => $otherStore->id,
            'category_id' => $otherCategory->id,
        ]);

        $response = $this->postJson('/api/cart/items', [
            'product_id' => $otherProduct->id,
            'quantity' => 1,
            'session_id' => 'session-cross-store',
        ], $this->storeHeaders());

        $response->assertStatus(403);
    }

    public function test_cannot_add_out_of_stock_product(): void
    {
        $category = Category::factory()->create(['store_id' => $this->store->id]);
        $outOfStock = Product::factory()->outOfStock()->create([
            'store_id' => $this->store->id,
            'category_id' => $category->id,
        ]);

        $response = $this->postJson('/api/cart/items', [
            'product_id' => $outOfStock->id,
            'quantity' => 1,
            'session_id' => 'session-oos',
        ], $this->storeHeaders());

        $response->assertStatus(422);
    }

    public function test_cannot_add_inactive_product(): void
    {
        $category = Category::factory()->create(['store_id' => $this->store->id]);
        $inactive = Product::factory()->inactive()->create([
            'store_id' => $this->store->id,
            'category_id' => $category->id,
        ]);

        $response = $this->postJson('/api/cart/items', [
            'product_id' => $inactive->id,
            'quantity' => 1,
            'session_id' => 'session-inactive',
        ], $this->storeHeaders());

        $response->assertStatus(422);
    }

    public function test_can_remove_item_from_cart(): void
    {
        // Add item first
        $addResponse = $this->postJson('/api/cart/items', [
            'product_id' => $this->product->id,
            'quantity' => 1,
            'session_id' => 'session-remove',
        ], $this->storeHeaders());

        $addResponse->assertStatus(201);

        $cartData = $addResponse->json('data');
        $itemId = $cartData['items'][0]['id'];

        // Remove item
        $response = $this->deleteJson("/api/cart/items/{$itemId}", [
            'session_id' => 'session-remove',
        ], $this->storeHeaders());

        $response->assertOk()
            ->assertJson(['message' => 'Item removido do carrinho.']);
    }

    public function test_can_update_item_quantity(): void
    {
        // Add item first
        $addResponse = $this->postJson('/api/cart/items', [
            'product_id' => $this->product->id,
            'quantity' => 1,
            'session_id' => 'session-update',
        ], $this->storeHeaders());

        $addResponse->assertStatus(201);

        $cartData = $addResponse->json('data');
        $itemId = $cartData['items'][0]['id'];

        // Update quantity
        $response = $this->patchJson("/api/cart/items/{$itemId}", [
            'quantity' => 5,
            'session_id' => 'session-update',
        ], $this->storeHeaders());

        $response->assertOk()
            ->assertJson(['message' => 'Quantidade atualizada.']);
    }
}
