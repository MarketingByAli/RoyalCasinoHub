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

        if ($market->participants()->where('user_id', $user->id)->exists()) {
            return true;
        }

        if (in_array($market->status, [MarketStatus::Open, MarketStatus::PartiallyMatched], true)
            && $market->visibility === 'public') {
            return true;
        }

        if ($market->status === MarketStatus::Open && $market->visibility === 'private_invite') {
            return $this->hasInviteAccess($market);
        }

        if ($market->status === MarketStatus::PartiallyMatched && $market->visibility === 'private_invite') {
            return $this->hasInviteAccess($market);
        }

        return false;
    }

    public function cancel(User $user, Market $market): bool
    {
        return $market->creator_id === $user->id;
    }

    public function accept(User $user, Market $market): bool
    {
        if ($market->creator_id === $user->id) {
            return false;
        }

        if (! in_array($market->status, [MarketStatus::Open, MarketStatus::PartiallyMatched], true)) {
            return false;
        }

        $market->loadMissing('creator');

        if ($user->isBlockedBy($market->creator) || $market->creator->isBlockedBy($user)) {
            return false;
        }

        if ($market->visibility === 'private_invite' && ! $this->hasInviteAccess($market)) {
            return false;
        }

        return true;
    }

    public function dispute(User $user, Market $market): bool
    {
        return $market->status === MarketStatus::DisputeWindow
            && $market->participants()->where('user_id', $user->id)->exists();
    }

    private function hasInviteAccess(Market $market): bool
    {
        $token = request()->input('invite_token')
            ?? request()->session()->get('betting.invite_tokens.'.$market->id);

        return is_string($token) && $token !== '' && hash_equals((string) $market->invite_token, $token);
    }
}
