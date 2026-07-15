<?php

namespace App\Betting\Policies;

use App\Betting\Models\Market;
use App\Models\User;

class MarketPolicy
{
    public function view(User $user, Market $market): bool
    {
        return $market->creator_id === $user->id
            || $market->challenger_id === $user->id
            || $market->status->value === 'open';
    }

    public function cancel(User $user, Market $market): bool
    {
        return $market->creator_id === $user->id;
    }

    public function accept(User $user, Market $market): bool
    {
        return $market->creator_id !== $user->id
            && $market->status->value === 'open'
            && ! $user->isBlockedBy($market->creator);
    }
}
