<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnrichmentQueue extends Model
{
    use HasFactory;

    protected $table = 'enrichment_queue';

    protected $fillable = [
        'casino_id',
        'job_type',
        'status',
        'attempts',
        'last_attempted_at',
        'result',
    ];

    protected $casts = [
        'last_attempted_at' => 'datetime',
    ];

    public function casino(): BelongsTo
    {
        return $this->belongsTo(Casino::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
