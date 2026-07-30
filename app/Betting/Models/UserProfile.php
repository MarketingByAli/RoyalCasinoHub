<?php

namespace App\Betting\Models;

use App\Betting\Enums\AccountState;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserProfile extends Model
{
    protected $fillable = [
        'user_id',
        'username',
        'display_name',
        'bio',
        'avatar_path',
        'country',
        'language',
        'date_of_birth',
        'account_state',
        'terms_accepted_at',
        'gambling_rules_accepted_at',
        'privacy_accepted_at',
        'marketing_consent_at',
        'responsible_gambling_ack_at',
        'customer_funds_ack_at',
        'referral_code',
        'referred_by_user_id',
        'referral_credited_at',
        'hide_wager_amounts',
        'hide_betting_activity',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'account_state' => AccountState::class,
            'terms_accepted_at' => 'datetime',
            'gambling_rules_accepted_at' => 'datetime',
            'privacy_accepted_at' => 'datetime',
            'marketing_consent_at' => 'datetime',
            'responsible_gambling_ack_at' => 'datetime',
            'customer_funds_ack_at' => 'datetime',
            'referral_credited_at' => 'datetime',
            'hide_wager_amounts' => 'boolean',
            'hide_betting_activity' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by_user_id');
    }

    public function followers(): HasMany
    {
        return $this->hasMany(Follower::class, 'following_id', 'user_id');
    }
}
