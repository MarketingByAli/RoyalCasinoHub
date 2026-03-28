<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CasinoReport extends Model
{
    protected $fillable = ['casino_id', 'user_id', 'reason', 'details', 'status'];

    public function casino(): BelongsTo
    {
        return $this->belongsTo(Casino::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
