<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CasinoNews extends Model
{
    use HasFactory;

    protected $table = 'casino_news';

    protected $fillable = [
        'casino_id',
        'title',
        'url',
        'source',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function casino(): BelongsTo
    {
        return $this->belongsTo(Casino::class);
    }
}
