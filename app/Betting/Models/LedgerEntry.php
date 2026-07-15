<?php

namespace App\Betting\Models;

use App\Betting\Enums\LedgerEntryType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LedgerEntry extends Model
{
    public $timestamps = false;

    protected $table = 'betting_ledger_entries';

    protected $fillable = [
        'wallet_id',
        'type',
        'amount',
        'balance_after_available',
        'balance_after_locked',
        'reference_type',
        'reference_id',
        'idempotency_key',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'type' => LedgerEntryType::class,
            'amount' => 'decimal:2',
            'balance_after_available' => 'decimal:2',
            'balance_after_locked' => 'decimal:2',
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }
}
