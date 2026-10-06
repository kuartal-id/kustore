<?php

namespace App\Policies;

use App\Models\StoreLink;
use App\Models\User;

class StoreLinkPolicy
{
    public function update(User $user, StoreLink $link): bool
    {
        return $user->store?->id === $link->store_id;
    }

    public function delete(User $user, StoreLink $link): bool
    {
        return $this->update($user, $link);
    }
}
