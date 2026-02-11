<?php

namespace App\Repositories;

use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    public function findByStoreId(int $storeId): Collection
    {
        return Category::where('store_id', $storeId)
            ->where('active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function findById(int $id): ?Category
    {
        return Category::find($id);
    }

    public function findBySlug(int $storeId, string $slug): ?Category
    {
        return Category::where('store_id', $storeId)->where('slug', $slug)->first();
    }

    public function create(array $data): Category
    {
        return Category::create($data);
    }

    public function update(Category $category, array $data): Category
    {
        $category->update($data);
        return $category->fresh();
    }

    public function delete(Category $category): bool
    {
        return $category->delete();
    }
}
