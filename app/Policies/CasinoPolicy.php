<?php

namespace App\Policies;

use App\Models\Casino;
use App\Models\User;

class CasinoPolicy
{
    public function create(User $user): bool
    {
        return $user->hasVerifiedEmail()
            && $user->is_active !== false
            && $user->role === 'casino_owner';
    }

    public function report(User $user, Casino $casino): bool
    {
        return $user->hasVerifiedEmail() && $user->is_active !== false;
    }
}
