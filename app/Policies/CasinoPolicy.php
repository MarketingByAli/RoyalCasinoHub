<?php

namespace App\Policies;

use App\Models\Casino;
use App\Models\User;

class CasinoPolicy
{
    public function create(User $user): bool
    {
        if (! $user->hasVerifiedEmail() || $user->is_active === false) {
            return false;
        }

        return $user->role === 'casino_owner' || $user->role === 'admin';
    }

    public function report(User $user, Casino $casino): bool
    {
        return $user->hasVerifiedEmail() && $user->is_active !== false;
    }
}
