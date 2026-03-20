<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Casino extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'country',
        'country_slug',
        'website',
        'logo_url',
        'logo_alt',
        'screenshot_url',
        'screenshot_alt',
        'email',
        'phone',
        'description',
        'short_description',
        'established_year',
        'license',
        'language',
        'software_providers',
        'meta_title',
        'meta_description',
        'canonical_url',
        'robots',
        'news_last_fetched_at',
    ];

    protected $casts = [
        'software_providers' => 'array',
        'schema_data' => 'array',
        'news_last_fetched_at' => 'datetime',
        'is_claimed' => 'boolean',
    ];

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('status', 'approved');
    }

    public function news(): HasMany
    {
        return $this->hasMany(CasinoNews::class, 'casino_id');
    }

    public function claimedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimed_by_user_id');
    }

    public function claimedListings(): HasMany
    {
        return $this->hasMany(ClaimedListing::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeByCountry($query, string $slug)
    {
        return $query->where('country_slug', $slug);
    }

    public function canBeEditedBy(User $user): bool
    {
        if ($user->role === 'admin') {
            return true;
        }
        return $this->is_claimed && $this->claimed_by_user_id === $user->id;
    }
}
