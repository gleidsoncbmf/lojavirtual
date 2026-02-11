<?php

namespace App\Contracts\Repositories;

use App\Models\Order;
use Illuminate\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface
{
    public function findByStoreId(int $storeId, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?Order;

    public function findByOrderNumber(string $orderNumber): ?Order;

    public function create(array $data): Order;

    public function update(Order $order, array $data): Order;
}
