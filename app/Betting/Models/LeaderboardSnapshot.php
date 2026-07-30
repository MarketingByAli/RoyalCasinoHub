<?php

namespace App\Betting\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaderboardSnapshot extends Model
{
    protected $table = 'betting_leaderboard_snapshots';

    protected $fillable = [
        'period',
        'period_start',
        'period_end',
        'user_id',
        'rank',
        'wins',
        'losses',
        'net_points',
        'settled_markets',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'net_points' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
