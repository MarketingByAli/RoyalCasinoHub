<?php

namespace App\Betting\Services;

use App\Betting\Enums\LedgerEntryType;
use App\Betting\Models\LedgerEntry;
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
            $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();

            if (! $wallet) {
                $wallet = $this->getOrCreateWallet($user);
                $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();
            }

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

            $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->firstOrFail();

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

            $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->firstOrFail();

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

    public function settleWinner(User $winner, float $lockedStake, float $totalPayout, string $referenceType, int $referenceId, string $idempotencyKey): Wallet
    {
        return DB::transaction(function () use ($winner, $lockedStake, $totalPayout, $referenceType, $referenceId, $idempotencyKey) {
            if (LedgerEntry::where('idempotency_key', $idempotencyKey)->exists()) {
                return $this->getOrCreateWallet($winner);
            }

            $wallet = Wallet::where('user_id', $winner->id)->lockForUpdate()->firstOrFail();

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

            $wallet = Wallet::where('user_id', $loser->id)->lockForUpdate()->firstOrFail();

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

    public function voidRefund(User $user, float $amount, string $referenceType, int $referenceId, string $idempotencyKey): Wallet
    {
        return $this->releaseStake($user, $amount, $referenceType, $referenceId, $idempotencyKey);
    }

    public function manualAdjust(User $user, float $amount, string $reason, User $admin): Wallet
    {
        return DB::transaction(function () use ($user, $amount, $reason, $admin) {
            $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->firstOrFail();
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
                'manual_adjust:'.$user->id.':'.now()->timestamp,
                User::class,
                $user->id,
                ['reason' => $reason, 'admin_id' => $admin->id, 'signed_amount' => $amount]
            );

            return $wallet->fresh();
        });
    }

    public function openLiability(User $user): float
    {
        $wallet = $this->getOrCreateWallet($user);

        return (float) $wallet->locked;
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
