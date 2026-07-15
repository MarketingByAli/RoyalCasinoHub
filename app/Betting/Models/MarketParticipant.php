<?php

namespace App\Betting\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketParticipant extends Model
{
    protected $table = 'betting_market_participants';

    protected $fillable = [
        'betting_market_id',
        'user_id',
        'role',
        'outcome',
        'stake_amount',
        'market_version_id',
        'accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'stake_amount' => 'decimal:2',
            'accepted_at' => 'datetime',
        ];
    }

    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class, 'betting_market_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(MarketVersion::class, 'market_version_id');
    }
}
