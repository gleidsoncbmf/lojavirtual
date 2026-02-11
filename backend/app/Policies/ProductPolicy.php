<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Product $product): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isStoreOwner() || $user->isPlatformAdmin();
    }

    public function update(User $user, Product $product): bool
    {
        if ($user->isPlatformAdmin())
            return true;
        return $user->isStoreOwner() && $user->store_id === $product->store_id;
    }

    public function delete(User $user, Product $product): bool
    {
        if ($user->isPlatformAdmin())
            return true;
        return $user->isStoreOwner() && $user->store_id === $product->store_id;
    }
}
