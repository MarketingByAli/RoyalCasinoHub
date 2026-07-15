<?php

namespace App\Betting\Services;

use App\Betting\Models\BettingNotification;
use App\Models\User;

class BettingNotificationService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function notify(User $user, string $type, array $data): BettingNotification
    {
        return BettingNotification::create([
            'user_id' => $user->id,
            'type' => $type,
            'data' => $data,
        ]);
    }
}
