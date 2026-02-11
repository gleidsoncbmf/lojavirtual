<?php

namespace App\Policies;

use App\Models\Store;
use App\Models\User;

class StorePolicy
{
    public function view(User $user, Store $store): bool
    {
        if ($user->isPlatformAdmin())
            return true;
        return $user->store_id === $store->id;
    }

    public function update(User $user, Store $store): bool
    {
        if ($user->isPlatformAdmin())
            return true;
        return $user->isStoreOwner() && $user->store_id === $store->id;
    }

    public function manage(User $user): bool
    {
        return $user->isPlatformAdmin();
    }
}
