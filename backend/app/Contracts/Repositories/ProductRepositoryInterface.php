<?php

namespace App\Contracts\Repositories;

use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    public function findByStoreId(int $storeId, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findBySlug(int $storeId, string $slug): ?Product;

    public function findById(int $id): ?Product;

    public function create(array $data): Product;

    public function update(Product $product, array $data): Product;

    public function delete(Product $product): bool;

    public function findByCategorySlug(int $storeId, string $categorySlug, int $perPage = 15): LengthAwarePaginator;
}
