<?php

namespace App\Repositories;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentProductRepository implements ProductRepositoryInterface
{
    public function findByStoreId(int $storeId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Product::where('store_id', $storeId)
            ->with(['category', 'variations']);

        if (isset($filters['active'])) {
            $query->where('active', $filters['active']);
        }

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('description', 'like', "%{$filters['search']}%");
            });
        }

        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDir = $filters['sort_dir'] ?? 'desc';
        $query->orderBy($sortBy, $sortDir);

        return $query->paginate($perPage);
    }

    public function findBySlug(int $storeId, string $slug): ?Product
    {
        return Product::where('store_id', $storeId)
            ->where('slug', $slug)
            ->with(['category', 'variations'])
            ->first();
    }

    public function findById(int $id): ?Product
    {
        return Product::with(['category', 'variations'])->find($id);
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);
        return $product->fresh(['category', 'variations']);
    }

    public function delete(Product $product): bool
    {
        return $product->delete();
    }

    public function findByCategorySlug(int $storeId, string $categorySlug, int $perPage = 15): LengthAwarePaginator
    {
        return Product::where('store_id', $storeId)
            ->where('active', true)
            ->whereHas('category', fn($q) => $q->where('slug', $categorySlug))
            ->with(['category', 'variations'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}
