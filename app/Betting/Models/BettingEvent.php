<?php

namespace App\Betting\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BettingEvent extends Model
{
    protected $table = 'betting_events';

    protected $fillable = [
        'title',
        'slug',
        'category',
        'organiser',
        'location',
        'start_at',
        'completes_at',
        'betting_close_at',
        'status',
        'settlement_source',
        'winning_outcome',
        'result_published_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'completes_at' => 'datetime',
            'betting_close_at' => 'datetime',
            'result_published_at' => 'datetime',
        ];
    }

    public function sources(): HasMany
    {
        return $this->hasMany(BettingEventSource::class, 'betting_event_id');
    }

    public function markets(): HasMany
    {
        return $this->hasMany(Market::class, 'betting_event_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeApprovedForBetting($query)
    {
        return $query->whereIn('status', ['scheduled', 'in_progress']);
    }
}
