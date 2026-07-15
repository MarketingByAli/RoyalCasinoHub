<?php

namespace App\Betting\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    protected $table = 'betting_wallets';

    protected $fillable = [
        'user_id',
        'currency',
        'available',
        'locked',
        'starter_grant_issued',
    ];

    protected function casts(): array
    {
        return [
            'available' => 'decimal:2',
            'locked' => 'decimal:2',
            'starter_grant_issued' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class, 'wallet_id');
    }
}
