<?php

namespace App\Models;

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

    public function favoriteCasinos(): BelongsToMany
    {
        return $this->belongsToMany(Casino::class, 'user_casino_favorites')->withTimestamps();
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
