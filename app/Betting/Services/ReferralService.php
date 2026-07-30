<?php

namespace App\Betting\Services;

use App\Betting\Enums\LedgerEntryType;
use App\Betting\Models\UserProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ReferralService
{
    public function __construct(
        private PlayWalletService $walletService,
        private BettingNotificationService $notifications,
    ) {}

    public function attributeReferral(User $referee, ?string $referralCode): void
    {
        if (! $referralCode) {
            return;
        }

        $profile = $referee->bettingProfile;
        if (! $profile || $profile->referred_by_user_id) {
            return;
        }

        $referrerProfile = UserProfile::where('referral_code', strtoupper(trim($referralCode)))->first();
        if (! $referrerProfile || $referrerProfile->user_id === $referee->id) {
            return;
        }

        $profile->referred_by_user_id = $referrerProfile->user_id;
        $profile->save();
    }

    public function creditIfEligible(User $referee): void
    {
        $profile = $referee->fresh()->bettingProfile;
        if (! $profile || ! $profile->referred_by_user_id || $profile->referral_credited_at) {
            return;
        }

        if (! $referee->hasVerifiedEmail()) {
            return;
        }

        $referrer = User::find($profile->referred_by_user_id);
        if (! $referrer) {
            return;
        }

        $bonus = (float) config('betting.referral_bonus_points', 250);

        DB::transaction(function () use ($referee, $referrer, $profile, $bonus) {
            $profile = UserProfile::where('id', $profile->id)->lockForUpdate()->firstOrFail();
            if ($profile->referral_credited_at) {
                return;
            }

            $this->walletService->creditAvailable(
                $referee,
                $bonus,
                LedgerEntryType::ReferralBonus,
                'referral_bonus:referee:'.$referee->id,
                User::class,
                $referrer->id,
                ['role' => 'referee']
            );

            $this->walletService->creditAvailable(
                $referrer,
                $bonus,
                LedgerEntryType::ReferralBonus,
                'referral_bonus:referrer:'.$referee->id,
                User::class,
                $referee->id,
                ['role' => 'referrer']
            );

            $profile->referral_credited_at = now();
            $profile->save();

            $this->notifications->notify($referee, 'referral_bonus', [
                'amount' => $bonus,
                'role' => 'referee',
            ]);
            $this->notifications->notify($referrer, 'referral_bonus', [
                'amount' => $bonus,
                'role' => 'referrer',
            ]);
        });
    }

    public function findCodeOwner(string $code): ?UserProfile
    {
        return UserProfile::where('referral_code', strtoupper(trim($code)))->first();
    }

    public function requireValidCode(string $code): UserProfile
    {
        $profile = $this->findCodeOwner($code);
        if (! $profile) {
            throw new RuntimeException('Invalid referral code.');
        }

        return $profile;
    }
}
