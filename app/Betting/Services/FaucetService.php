<?php

namespace App\Betting\Services;

use App\Betting\Enums\LedgerEntryType;
use App\Betting\Models\FaucetClaim;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FaucetService
{
    public function __construct(
        private PlayWalletService $walletService,
        private BettingNotificationService $notifications,
    ) {}

    public function canClaim(User $user): bool
    {
        return $this->nextClaimAt($user) === null || $this->nextClaimAt($user)->isPast();
    }

    public function nextClaimAt(User $user): ?\Carbon\CarbonInterface
    {
        $last = FaucetClaim::where('user_id', $user->id)->latest('claimed_at')->first();
        if (! $last) {
            return null;
        }

        $hours = (int) config('betting.faucet_cooldown_hours', 24);

        return $last->claimed_at->copy()->addHours($hours);
    }

    public function claim(User $user): FaucetClaim
    {
        if (! $this->canClaim($user)) {
            throw new RuntimeException('Faucet is on cooldown. Try again later.');
        }

        $amount = (float) config('betting.faucet_points', 500);

        return DB::transaction(function () use ($user, $amount) {
            if (! $this->canClaim($user)) {
                throw new RuntimeException('Faucet is on cooldown. Try again later.');
            }

            $claim = FaucetClaim::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'claimed_at' => now(),
            ]);

            $this->walletService->creditAvailable(
                $user,
                $amount,
                LedgerEntryType::Faucet,
                'faucet:'.$user->id.':'.$claim->id,
                FaucetClaim::class,
                $claim->id
            );

            $this->notifications->notify($user, 'faucet_claimed', [
                'amount' => $amount,
            ]);

            return $claim;
        });
    }
}
