<?php

namespace App\Betting\Policies;

use App\Betting\Enums\MarketStatus;
use App\Betting\Models\Market;
use App\Models\User;

class MarketPolicy
{
    public function view(?User $user, Market $market): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->role === 'admin') {
            return true;
        }

        if ($market->creator_id === $user->id || $market->challenger_id === $user->id) {
            return true;
        }

        return $market->status === MarketStatus::Open;
    }

    public function cancel(User $user, Market $market): bool
    {
        return $market->creator_id === $user->id;
    }

    public function accept(User $user, Market $market): bool
    {
        if ($market->creator_id === $user->id || $market->status !== MarketStatus::Open) {
            return false;
        }

        $market->loadMissing('creator');

        return ! $user->isBlockedBy($market->creator)
            && ! $market->creator->isBlockedBy($user);
    }

    public function dispute(User $user, Market $market): bool
    {
        return $market->status === MarketStatus::DisputeWindow
            && $market->participants()->where('user_id', $user->id)->exists();
    }
}
