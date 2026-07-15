<?php

namespace App\Models;

use App\Betting\Models\Dispute;
use App\Betting\Models\Follower;
use App\Betting\Models\Market;
use App\Betting\Models\UserBlock;
use App\Betting\Models\UserProfile;
use App\Betting\Models\UserReport;
use App\Betting\Models\Wallet;
use App\Notifications\QueuedResetPassword;
use App\Notifications\QueuedVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'is_active', 'settings', 'reviewer_credibility_score'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements CanResetPasswordContract, MustVerifyEmailContract
{
    /** @use HasFactory<UserFactory> */
    use CanResetPassword, HasFactory, MustVerifyEmail, Notifiable, SoftDeletes;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'settings' => 'array',
            'reviewer_credibility_score' => 'decimal:2',
        ];
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new QueuedResetPassword($token));
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new QueuedVerifyEmail);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function claimedListings(): HasMany
    {
        return $this->hasMany(ClaimedListing::class);
    }

    public function claimedCasinos(): HasMany
    {
        return $this->hasMany(Casino::class, 'claimed_by_user_id');
    }

    public function submittedCasinos(): HasMany
    {
        return $this->hasMany(Casino::class, 'submitted_by_user_id');
    }

    public function favoriteCasinos(): BelongsToMany
    {
        return $this->belongsToMany(Casino::class, 'user_casino_favorites')->withTimestamps();
    }

    public function bettingProfile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function bettingWallet(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function createdMarkets(): HasMany
    {
        return $this->hasMany(Market::class, 'creator_id');
    }

    public function bettingDisputes(): HasMany
    {
        return $this->hasMany(Dispute::class);
    }

    public function followers(): HasMany
    {
        return $this->hasMany(Follower::class, 'following_id');
    }

    public function following(): HasMany
    {
        return $this->hasMany(Follower::class, 'follower_id');
    }

    public function blockedUsers(): HasMany
    {
        return $this->hasMany(UserBlock::class, 'blocker_id');
    }

    public function reportsMade(): HasMany
    {
        return $this->hasMany(UserReport::class, 'reporter_id');
    }

    public function isBlockedBy(User $user): bool
    {
        return UserBlock::where('blocker_id', $user->id)->where('blocked_id', $this->id)->exists();
    }

    /**
     * Reviewer credibility v1: base 1.0, +0.12 per approved review (cap growth),
     * +up to 0.48 from aggregate helpful votes on those reviews. Clamped to [1, 5].
     */
    public function recalculateReviewerCredibility(): void
    {
        $approved = $this->reviews()->where('status', 'approved')->count();
        $helpfulUps = (int) $this->reviews()->where('status', 'approved')->sum('helpful_up_count');
        $base = 1 + ($approved * 0.12) + min(0.48, $helpfulUps * 0.02);

        $this->reviewer_credibility_score = min(5, max(1, $base));
        $this->saveQuietly();
    }
}
