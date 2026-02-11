<?php

namespace App\Contracts\Repositories;

use App\Models\Store;
use Illuminate\Pagination\LengthAwarePaginator;

interface StoreRepositoryInterface
{
    public function findAll(int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?Store;

    public function findBySlug(string $slug): ?Store;

    public function findByDomain(string $domain): ?Store;

    public function create(array $data): Store;

    public function update(Store $store, array $data): Store;
}
