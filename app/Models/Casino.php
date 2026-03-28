<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Casino extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'country',
        'country_slug',
        'region',
        'locality',
        'website',
        'social_links',
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
        'license_authority_slug',
        'language',
        'software_providers',
        'payment_methods',
        'support_channels',
        'pros',
        'cons',
        'min_deposit',
        'withdrawal_time_text',
        'last_verified_at',
        'profile_completeness',
        'gallery_urls',
        'tier',
        'featured_until',
        'website_last_checked_at',
        'website_link_broken',
        'enrichment_last_error',
        'rating_avg_trust',
        'rating_avg_games',
        'rating_avg_support',
        'rating_avg_payments',
        'rating_avg_bonuses',
        'meta_title',
        'meta_description',
        'canonical_url',
        'robots',
        'news_last_fetched_at',
    ];

    protected $casts = [
        'social_links' => 'array',
        'software_providers' => 'array',
        'payment_methods' => 'array',
        'support_channels' => 'array',
        'gallery_urls' => 'array',
        'schema_data' => 'array',
        'news_last_fetched_at' => 'datetime',
        'last_verified_at' => 'datetime',
        'featured_until' => 'datetime',
        'website_last_checked_at' => 'datetime',
        'is_claimed' => 'boolean',
        'website_link_broken' => 'boolean',
        'min_deposit' => 'decimal:2',
        'profile_completeness' => 'integer',
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

    public function offers(): HasMany
    {
        return $this->hasMany(CasinoOffer::class);
    }

    public function activeOffers(): HasMany
    {
        return $this->offers()->where('is_active', true);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'casino_tag')->withTimestamps();
    }

    public function dailyViews(): HasMany
    {
        return $this->hasMany(CasinoDailyView::class);
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

    public function scopeFeatured($query)
    {
        return $query->whereNotNull('featured_until')->where('featured_until', '>', now());
    }

    public function getShowsVerifiedBadgeAttribute(): bool
    {
        return $this->is_claimed && ($this->profile_completeness ?? 0) >= 60;
    }

    public function recalculateProfileCompleteness(): void
    {
        $pts = 0;
        if (strlen((string) $this->description) > 50) {
            $pts += 25;
        }
        if ($this->logo_url) {
            $pts += 15;
        }
        if ($this->website) {
            $pts += 10;
        }
        if ($this->license || $this->license_authority_slug) {
            $pts += 10;
        }
        if (! empty($this->payment_methods)) {
            $pts += 10;
        }
        if ($this->pros || $this->cons) {
            $pts += 10;
        }
        if ($this->established_year) {
            $pts += 5;
        }
        if ($this->offers()->where('is_active', true)->exists()) {
            $pts += 10;
        }
        if (! empty($this->gallery_urls)) {
            $pts += 5;
        }
        $this->profile_completeness = min(100, $pts);
    }

    public function recalculateDimensionAverages(): void
    {
        $dims = ['trust', 'games', 'support', 'payments', 'bonuses'];
        $sums = array_fill_keys($dims, 0);
        $counts = array_fill_keys($dims, 0);

        foreach ($this->approvedReviews()->get() as $review) {
            $dr = $review->dimension_ratings ?? [];
            foreach ($dims as $d) {
                if (isset($dr[$d]) && is_numeric($dr[$d])) {
                    $sums[$d] += (int) $dr[$d];
                    $counts[$d]++;
                }
            }
        }

        foreach ($dims as $d) {
            $col = 'rating_avg_'.$d;
            $this->{$col} = $counts[$d] > 0 ? round($sums[$d] / $counts[$d], 2) : null;
        }
    }

    public function canBeEditedBy(User $user): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $this->is_claimed && $this->claimed_by_user_id === $user->id;
    }

    /**
     * @param  array<string, string>|null  $existing
     * @return array<string, string>|null
     */
    public static function mergeSocialLinks(?array $existing, ?string $linkedinUrl): ?array
    {
        $links = $existing ?? [];
        $linkedinUrl = $linkedinUrl !== null ? trim($linkedinUrl) : '';
        if ($linkedinUrl === '') {
            unset($links['linkedin']);
        } else {
            $links['linkedin'] = $linkedinUrl;
        }

        return $links === [] ? null : $links;
    }
}
