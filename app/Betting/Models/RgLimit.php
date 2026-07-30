<?php

namespace App\Betting\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RgLimit extends Model
{
    protected $table = 'betting_rg_limits';

    protected $fillable = [
        'user_id',
        'daily_stake_limit',
        'weekly_stake_limit',
    ];

    protected function casts(): array
    {
        return [
            'daily_stake_limit' => 'decimal:2',
            'weekly_stake_limit' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
