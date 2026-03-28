<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CasinoOffer extends Model
{
    protected $fillable = [
        'casino_id', 'title', 'welcome_bonus_text', 'wagering_requirement',
        'free_spins', 'expires_at', 'source', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
            'free_spins' => 'integer',
        ];
    }

    public function casino(): BelongsTo
    {
        return $this->belongsTo(Casino::class);
    }
}
