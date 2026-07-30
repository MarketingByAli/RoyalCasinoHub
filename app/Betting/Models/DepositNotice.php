<?php

namespace App\Betting\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepositNotice extends Model
{
    protected $table = 'betting_deposit_notices';

    protected $fillable = [
        'user_id',
        'deposit_method_id',
        'amount',
        'tx_hash',
        'user_note',
        'status',
        'credited_amount',
        'admin_note',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'credited_amount' => 'decimal:2',
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function method(): BelongsTo
    {
        return $this->belongsTo(DepositMethod::class, 'deposit_method_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
