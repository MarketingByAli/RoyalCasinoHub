<?php

namespace App\Betting\Models;

use App\Betting\Enums\MarketFormat;
use App\Betting\Enums\MarketStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Market extends Model
{
    protected $table = 'betting_markets';

    protected $fillable = [
        'uuid',
        'creator_id',
        'betting_event_id',
        'title',
        'description',
        'format',
        'outcome_options',
        'creator_outcome',
        'stake_amount',
        'participant_cap',
        'min_participants',
        'status',
        'visibility',
        'invite_token',
        'platform_fee_percent',
        'betting_close_at',
        'dispute_window_hours',
        'winning_outcome',
        'review_flags',
        'rejection_reason',
        'current_version_id',
        'expires_at',
        'dispute_window_ends_at',
        'challenger_id',
    ];

    protected function casts(): array
    {
        return [
            'format' => MarketFormat::class,
            'outcome_options' => 'array',
            'stake_amount' => 'decimal:2',
            'participant_cap' => 'integer',
            'min_participants' => 'integer',
            'status' => MarketStatus::class,
            'platform_fee_percent' => 'decimal:2',
            'betting_close_at' => 'datetime',
            'expires_at' => 'datetime',
            'dispute_window_ends_at' => 'datetime',
            'review_flags' => 'array',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function challenger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'challenger_id');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(BettingEvent::class, 'betting_event_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(MarketVersion::class, 'betting_market_id');
    }

    public function currentVersion(): BelongsTo
    {
        return $this->belongsTo(MarketVersion::class, 'current_version_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(MarketParticipant::class, 'betting_market_id');
    }

    public function disputes(): HasMany
    {
        return $this->hasMany(Dispute::class, 'betting_market_id');
    }

    public function challengerOutcome(): ?string
    {
        $options = $this->outcome_options ?? [];
        foreach ($options as $option) {
            if ($option !== $this->creator_outcome) {
                return $option;
            }
        }

        return null;
    }

    public function isMatched(): bool
    {
        return in_array($this->status, [
            MarketStatus::FullyMatched,
            MarketStatus::Locked,
            MarketStatus::InProgress,
            MarketStatus::PendingResult,
            MarketStatus::ResultPublished,
            MarketStatus::DisputeWindow,
            MarketStatus::UnderDispute,
            MarketStatus::Settled,
        ], true);
    }

    public function activeParticipants(): HasMany
    {
        return $this->participants()->where('status', 'active');
    }

    public function isPool(): bool
    {
        return (int) $this->participant_cap > 2;
    }

    public function isPublic(): bool
    {
        return $this->visibility === 'public';
    }
}
