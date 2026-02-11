<?php

namespace App\Services;

use App\DTOs\ProductDTO;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductService
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
    ) {
    }

    public function getProducts(int $storeId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->productRepository->findByStoreId($storeId, $filters, $perPage);
    }

    public function getProductBySlug(int $storeId, string $slug): ?Product
    {
        return $this->productRepository->findBySlug($storeId, $slug);
    }

    public function createProduct(Store $store, ProductDTO $dto): Product
    {
        $data = array_merge($dto->toArray(), ['store_id' => $store->id]);
        return $this->productRepository->create($data);
    }

    public function updateProduct(Product $product, ProductDTO $dto): Product
    {
        return $this->productRepository->update($product, $dto->toArray());
    }

    public function deleteProduct(Product $product): bool
    {
        return $this->productRepository->delete($product);
    }

    public function getProductsByCategory(int $storeId, string $categorySlug, int $perPage = 15): LengthAwarePaginator
    {
        return $this->productRepository->findByCategorySlug($storeId, $categorySlug, $perPage);
    }
}
