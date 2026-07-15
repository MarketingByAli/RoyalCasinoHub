<?php

namespace App\Betting\Services;

use App\Betting\Models\BettingNotification;
use App\Betting\Notifications\BettingAlert;
use App\Models\User;

class BettingNotificationService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function notify(User $user, string $type, array $data): BettingNotification
    {
        $record = BettingNotification::create([
            'user_id' => $user->id,
            'type' => $type,
            'data' => $data,
        ]);

        $user->notify(new BettingAlert($type, $data));

        return $record;
    }
}
