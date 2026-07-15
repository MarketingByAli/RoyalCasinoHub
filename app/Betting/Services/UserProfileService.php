<?php

namespace App\Betting\Services;

use App\Betting\Enums\AccountState;
use App\Betting\Models\UserProfile;
use App\Models\User;
use Illuminate\Support\Str;

class UserProfileService
{
    public function __construct(
        private PlayWalletService $walletService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createForUser(User $user, array $data): UserProfile
    {
        $profile = UserProfile::create([
            'user_id' => $user->id,
            'username' => $data['username'],
            'display_name' => $data['display_name'] ?? $user->name,
            'country' => $data['country'],
            'language' => $data['language'] ?? 'en',
            'date_of_birth' => $data['date_of_birth'],
            'account_state' => AccountState::Unverified,
            'terms_accepted_at' => now(),
            'gambling_rules_accepted_at' => ($data['accept_gambling_rules'] ?? false) ? now() : null,
            'privacy_accepted_at' => ($data['accept_privacy'] ?? false) ? now() : null,
            'marketing_consent_at' => ($data['accept_marketing'] ?? false) ? now() : null,
            'responsible_gambling_ack_at' => ($data['accept_responsible_gambling'] ?? false) ? now() : null,
            'customer_funds_ack_at' => ($data['accept_customer_funds'] ?? false) ? now() : null,
            'referral_code' => strtoupper(Str::random(8)),
        ]);

        $this->walletService->getOrCreateWallet($user);

        return $profile;
    }

    public function markEmailVerified(User $user): void
    {
        $profile = $user->bettingProfile;
        if (! $profile) {
            return;
        }

        $profile->account_state = AccountState::PlayOnly;
        $profile->save();

        $this->walletService->grantStarterPoints($user);
    }
}
