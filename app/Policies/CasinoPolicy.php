<?php

namespace App\Policies;

use App\Models\Casino;
use App\Models\User;

class CasinoPolicy
{
    public function report(User $user, Casino $casino): bool
    {
        return $user->hasVerifiedEmail() && $user->is_active !== false;
    }
}
