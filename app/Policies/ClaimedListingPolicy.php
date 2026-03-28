<?php

namespace App\Policies;

use App\Models\ClaimedListing;
use App\Models\User;

class ClaimedListingPolicy
{
    public function create(User $user): bool
    {
        return $user->hasVerifiedEmail() && ($user->is_active !== false);
    }

    public function update(User $user, ClaimedListing $claim): bool
    {
        return false;
    }
}
