<?php

namespace App\Betting\Services;

use App\Betting\Enums\LedgerEntryType;
use App\Betting\Enums\MarketStatus;
use App\Betting\Models\LedgerEntry;
use App\Betting\Models\Market;
use App\Betting\Models\Wallet;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PlayWalletService
{
    public function getOrCreateWallet(User $user): Wallet
    {
        return Wallet::firstOrCreate(
            ['user_id' => $user->id],
            [
                'currency' => config('betting.currency', 'POINTS'),
                'available' => 0,
                'locked' => 0,
            ]
        );
    }

    public function grantStarterPoints(User $user): bool
    {
        return DB::transaction(function () use ($user) {
            $wallet = $this->lockWalletForUser($user);

            if ($wallet->starter_grant_issued) {
                return false;
            }

            $amount = (float) config('betting.starter_points', 10000);
            $wallet->available = bcadd((string) $wallet->available, (string) $amount, 2);
            $wallet->starter_grant_issued = true;
            $wallet->save();

            $this->recordEntry(
                $wallet,
                LedgerEntryType::Grant,
                $amount,
                'starter_grant:'.$user->id,
                null,
                null,
                ['reason' => 'email_verified_starter_grant']
            );

            return true;
        });
    }

    public function lockStake(User $user, float $amount, string $referenceType, int $referenceId, string $idempotencyKey): Wallet
    {
        if ($amount <= 0) {
            throw new RuntimeException('Stake amount must be positive.');
        }

        return DB::transaction(function () use ($user, $amount, $referenceType, $referenceId, $idempotencyKey) {
            if (LedgerEntry::where('idempotency_key', $idempotencyKey)->exists()) {
                return $this->getOrCreateWallet($user);
            }

            $wallet = $this->lockWalletForUser($user);

            $maxLiability = (float) config('betting.max_open_liability_per_user', 20000);
            if ($this->totalExposure($user, $wallet) + $amount > $maxLiability) {
                throw new RuntimeException('Maximum exposure limit exceeded.');
            }

            if (bccomp((string) $wallet->available, (string) $amount, 2) < 0) {
                throw new RuntimeException('Insufficient available balance.');
            }

            $wallet->available = bcsub((string) $wallet->available, (string) $amount, 2);
            $wallet->locked = bcadd((string) $wallet->locked, (string) $amount, 2);
            $wallet->save();

            $this->recordEntry(
                $wallet,
                LedgerEntryType::StakeLock,
                $amount,
                $idempotencyKey,
                $referenceType,
                $referenceId
            );

            return $wallet->fresh();
        });
    }

    public function releaseStake(User $user, float $amount, string $referenceType, int $referenceId, string $idempotencyKey): Wallet
    {
        return DB::transaction(function () use ($user, $amount, $referenceType, $referenceId, $idempotencyKey) {
            if (LedgerEntry::where('idempotency_key', $idempotencyKey)->exists()) {
                return $this->getOrCreateWallet($user);
            }

            $wallet = $this->lockWalletForUser($user);

            if (bccomp((string) $wallet->locked, (string) $amount, 2) < 0) {
                throw new RuntimeException('Insufficient locked balance.');
            }

            $wallet->locked = bcsub((string) $wallet->locked, (string) $amount, 2);
            $wallet->available = bcadd((string) $wallet->available, (string) $amount, 2);
            $wallet->save();

            $this->recordEntry(
                $wallet,
                LedgerEntryType::StakeRelease,
                $amount,
                $idempotencyKey,
                $referenceType,
                $referenceId
            );

            return $wallet->fresh();
        });
    }

    public function voidRefund(User $user, float $amount, string $referenceType, int $referenceId, string $idempotencyKey): Wallet
    {
        return DB::transaction(function () use ($user, $amount, $referenceType, $referenceId, $idempotencyKey) {
            if (LedgerEntry::where('idempotency_key', $idempotencyKey)->exists()) {
                return $this->getOrCreateWallet($user);
            }

            $wallet = $this->lockWalletForUser($user);

            if (bccomp((string) $wallet->locked, (string) $amount, 2) < 0) {
                throw new RuntimeException('Insufficient locked balance for void refund.');
            }

            $wallet->locked = bcsub((string) $wallet->locked, (string) $amount, 2);
            $wallet->available = bcadd((string) $wallet->available, (string) $amount, 2);
            $wallet->save();

            $this->recordEntry(
                $wallet,
                LedgerEntryType::VoidRefund,
                $amount,
                $idempotencyKey,
                $referenceType,
                $referenceId
            );

            return $wallet->fresh();
        });
    }

    public function settleWinner(User $winner, float $lockedStake, float $totalPayout, string $referenceType, int $referenceId, string $idempotencyKey): Wallet
    {
        return DB::transaction(function () use ($winner, $lockedStake, $totalPayout, $referenceType, $referenceId, $idempotencyKey) {
            if (LedgerEntry::where('idempotency_key', $idempotencyKey)->exists()) {
                return $this->getOrCreateWallet($winner);
            }

            $wallet = $this->lockWalletForUser($winner);

            if (bccomp((string) $wallet->locked, (string) $lockedStake, 2) < 0) {
                throw new RuntimeException('Insufficient locked balance for winner settlement.');
            }

            $wallet->locked = bcsub((string) $wallet->locked, (string) $lockedStake, 2);
            $wallet->available = bcadd((string) $wallet->available, (string) $totalPayout, 2);
            $wallet->save();

            $this->recordEntry(
                $wallet,
                LedgerEntryType::SettlementCredit,
                $totalPayout,
                $idempotencyKey,
                $referenceType,
                $referenceId,
                ['locked_stake_released' => $lockedStake]
            );

            return $wallet->fresh();
        });
    }

    public function settleLoser(User $loser, float $amount, string $referenceType, int $referenceId, string $idempotencyKey): Wallet
    {
        return DB::transaction(function () use ($loser, $amount, $referenceType, $referenceId, $idempotencyKey) {
            if (LedgerEntry::where('idempotency_key', $idempotencyKey)->exists()) {
                return $this->getOrCreateWallet($loser);
            }

            $wallet = $this->lockWalletForUser($loser);

            if (bccomp((string) $wallet->locked, (string) $amount, 2) < 0) {
                throw new RuntimeException('Insufficient locked balance for settlement.');
            }

            $wallet->locked = bcsub((string) $wallet->locked, (string) $amount, 2);
            $wallet->save();

            $this->recordEntry(
                $wallet,
                LedgerEntryType::SettlementDebit,
                $amount,
                $idempotencyKey,
                $referenceType,
                $referenceId
            );

            return $wallet->fresh();
        });
    }

    public function manualAdjust(User $user, float $amount, string $reason, User $admin, string $idempotencyKey): Wallet
    {
        return DB::transaction(function () use ($user, $amount, $reason, $admin, $idempotencyKey) {
            if (LedgerEntry::where('idempotency_key', $idempotencyKey)->exists()) {
                return $this->getOrCreateWallet($user);
            }

            $wallet = $this->lockWalletForUser($user);
            $newAvailable = bcadd((string) $wallet->available, (string) $amount, 2);

            if (bccomp($newAvailable, '0', 2) < 0) {
                throw new RuntimeException('Adjustment would create negative balance.');
            }

            $wallet->available = $newAvailable;
            $wallet->save();

            $this->recordEntry(
                $wallet,
                LedgerEntryType::ManualAdjustment,
                abs($amount),
                $idempotencyKey,
                User::class,
                $user->id,
                ['reason' => $reason, 'admin_id' => $admin->id, 'signed_amount' => $amount]
            );

            return $wallet->fresh();
        });
    }

    public function openLiability(User $user): float
    {
        return (float) ($this->getOrCreateWallet($user)->locked ?? 0);
    }

    public function pendingOpenExposure(User $user): float
    {
        // Open challenges hard-reserve into locked; only count soft exposure for legacy unlocked opens.
        return (float) Market::query()
            ->where('creator_id', $user->id)
            ->where('status', MarketStatus::Open)
            ->get()
            ->filter(fn (Market $market) => ! $this->hasActiveCreatorReserve($market))
            ->sum(fn (Market $market) => (float) $market->stake_amount);
    }

    public function totalExposure(User $user, ?Wallet $wallet = null): float
    {
        $wallet ??= $this->getOrCreateWallet($user);

        return (float) $wallet->locked + $this->pendingOpenExposure($user);
    }

    public function hasStakeLockForMarket(User $user, Market $market, string $role): bool
    {
        return LedgerEntry::query()
            ->whereHas('wallet', fn ($q) => $q->where('user_id', $user->id))
            ->where('idempotency_key', 'stake_lock:market:'.$market->id.':'.$role)
            ->exists();
    }

    public function hasActiveCreatorReserve(Market $market): bool
    {
        if (! $this->hasStakeLockForMarket($market->creator, $market, 'creator')) {
            return false;
        }

        return ! LedgerEntry::query()
            ->whereHas('wallet', fn ($q) => $q->where('user_id', $market->creator_id))
            ->whereIn('idempotency_key', [
                'stake_release:market:'.$market->id.':creator',
                'void_refund:market:'.$market->id.':user:'.$market->creator_id,
                'settle_debit:market:'.$market->id.':user:'.$market->creator_id,
                'settle_credit:market:'.$market->id.':user:'.$market->creator_id,
            ])
            ->exists();
    }

    private function lockWalletForUser(User $user): Wallet
    {
        $this->getOrCreateWallet($user);

        return Wallet::where('user_id', $user->id)->lockForUpdate()->firstOrFail();
    }

    private function recordEntry(
        Wallet $wallet,
        LedgerEntryType $type,
        float $amount,
        string $idempotencyKey,
        ?string $referenceType,
        ?int $referenceId,
        array $metadata = []
    ): LedgerEntry {
        return LedgerEntry::create([
            'wallet_id' => $wallet->id,
            'type' => $type,
            'amount' => $amount,
            'balance_after_available' => $wallet->available,
            'balance_after_locked' => $wallet->locked,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'idempotency_key' => $idempotencyKey,
            'metadata' => $metadata ?: null,
        ]);
    }
}
