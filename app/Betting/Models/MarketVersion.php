<?php

namespace App\Betting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketVersion extends Model
{
    public $timestamps = false;

    protected $table = 'betting_market_versions';

    protected $fillable = [
        'betting_market_id',
        'version',
        'terms_hash',
        'terms_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'terms_snapshot' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class, 'betting_market_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(MarketParticipant::class, 'market_version_id');
    }
}
