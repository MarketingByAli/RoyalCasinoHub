<?php

namespace App\Betting\Models;

use App\Betting\Enums\ParticipantStatus;
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
        'status',
        'outcome',
        'stake_amount',
        'proposed_stake_amount',
        'proposed_outcome',
        'market_version_id',
        'accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ParticipantStatus::class,
            'stake_amount' => 'decimal:2',
            'proposed_stake_amount' => 'decimal:2',
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
