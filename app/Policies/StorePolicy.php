<?php

namespace App\Policies;

use App\Models\Store;
use App\Models\User;

class StorePolicy
{
    public function view(User $user, Store $store): bool
    {
        return $user->id === $store->user_id;
    }

    public function update(User $user, Store $store): bool
    {
        return $user->id === $store->user_id;
    }

    public function publish(User $user, Store $store): bool
    {
        return $user->id === $store->user_id && $user->canPublishStore() && ! $store->is_suspended;
    }

    public function suspend(User $user, Store $store): bool
    {
        return (bool) $user->is_admin;
    }
}
