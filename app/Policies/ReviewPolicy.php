<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function create(User $user): bool
    {
        return $user->hasVerifiedEmail() && ($user->is_active !== false);
    }

    public function update(User $user, Review $review): bool
    {
        return false;
    }

    public function vote(User $user, Review $review): bool
    {
        if ($review->status !== 'approved') {
            return false;
        }
        if ($review->user_id === $user->id) {
            return false;
        }

        return $user->hasVerifiedEmail() && $user->is_active !== false;
    }

    public function report(User $user, Review $review): bool
    {
        if ($review->status !== 'approved') {
            return false;
        }
        if ($review->user_id === $user->id) {
            return false;
        }

        return $user->hasVerifiedEmail() && $user->is_active !== false;
    }

    public function reply(User $user, Review $review): bool
    {
        if ($review->status !== 'approved') {
            return false;
        }

        $casino = $review->relationLoaded('casino') ? $review->casino : $review->casino()->first();
        if (! $casino || ! $casino->is_claimed || $casino->claimed_by_user_id !== $user->id) {
            return false;
        }

        return $user->hasVerifiedEmail() && $user->is_active !== false;
    }
}
