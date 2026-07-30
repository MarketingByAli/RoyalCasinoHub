<?php

namespace App\Betting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisputeAttachment extends Model
{
    protected $table = 'betting_dispute_attachments';

    protected $fillable = [
        'betting_dispute_id',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size_bytes',
    ];

    public function dispute(): BelongsTo
    {
        return $this->belongsTo(Dispute::class, 'betting_dispute_id');
    }
}
