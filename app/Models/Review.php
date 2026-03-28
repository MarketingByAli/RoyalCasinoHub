<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'casino_id',
        'user_id',
        'rating',
        'dimension_ratings',
        'title',
        'content',
        'status',
        'admin_internal_note',
        'helpful_up_count',
        'helpful_down_count',
    ];

    protected function casts(): array
    {
        return [
            'dimension_ratings' => 'array',
        ];
    }

    public function casino(): BelongsTo
    {
        return $this->belongsTo(Casino::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(ReviewReport::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(ReviewVote::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ReviewReply::class);
    }

    public function ownerReply(): HasOne
    {
        return $this->hasOne(ReviewReply::class)->where('status', 'approved')->latestOfMany();
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeMostHelpful($query)
    {
        return $query->orderByDesc('helpful_up_count')->orderByDesc('created_at');
    }
}
