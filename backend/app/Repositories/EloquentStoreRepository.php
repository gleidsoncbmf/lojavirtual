<?php

namespace App\Repositories;

use App\Contracts\Repositories\StoreRepositoryInterface;
use App\Models\Store;
use App\Models\StoreDomain;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentStoreRepository implements StoreRepositoryInterface
{
    public function findAll(int $perPage = 15): LengthAwarePaginator
    {
        return Store::with(['theme', 'primaryDomain'])->paginate($perPage);
    }

    public function findById(int $id): ?Store
    {
        return Store::with(['theme', 'domains'])->find($id);
    }

    public function findBySlug(string $slug): ?Store
    {
        return Store::where('slug', $slug)->with(['theme', 'domains'])->first();
    }

    public function findByDomain(string $domain): ?Store
    {
        $storeDomain = StoreDomain::where('domain', $domain)->first();

        if ($storeDomain) {
            return $storeDomain->store->load(['theme', 'domains']);
        }

        // Try to match by slug subdomain (e.g., mystore.platform.com)
        $slug = explode('.', $domain)[0];
        return Store::where('slug', $slug)->with(['theme', 'domains'])->first();
    }

    public function create(array $data): Store
    {
        return Store::create($data);
    }

    public function update(Store $store, array $data): Store
    {
        $store->update($data);
        return $store->fresh(['theme', 'domains']);
    }
}
