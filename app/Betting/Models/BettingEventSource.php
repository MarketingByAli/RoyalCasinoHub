<?php

namespace App\Betting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BettingEventSource extends Model
{
    protected $table = 'betting_event_sources';

    protected $fillable = [
        'betting_event_id',
        'provider_name',
        'external_id',
        'priority',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(BettingEvent::class, 'betting_event_id');
    }
}
