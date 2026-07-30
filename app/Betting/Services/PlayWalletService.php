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
            $idempotencyKey = 'starter_grant:'.$user->id;

            if ($wallet->starter_grant_issued || LedgerEntry::where('idempotency_key', $idempotencyKey)->exists()) {
                if (! $wallet->starter_grant_issued) {
                    $wallet->starter_grant_issued = true;
                    $wallet->save();
                }

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
                $idempotencyKey,
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

    public function stakeLockKey(Market $market, User $user): string
    {
        return 'stake_lock:market:'.$market->id.':user:'.$user->id;
    }

    public function stakeReleaseKey(Market $market, User $user): string
    {
        return 'stake_release:market:'.$market->id.':user:'.$user->id;
    }

    /**
     * Dual-read: user-scoped key preferred, legacy role keys still recognized.
     */
    public function hasStakeLockForMarket(User $user, Market $market, ?string $legacyRole = null): bool
    {
        $keys = [$this->stakeLockKey($market, $user)];

        if ($legacyRole) {
            $keys[] = 'stake_lock:market:'.$market->id.':'.$legacyRole;
        } else {
            $keys[] = 'stake_lock:market:'.$market->id.':creator';
            $keys[] = 'stake_lock:market:'.$market->id.':challenger';
        }

        return LedgerEntry::query()
            ->whereHas('wallet', fn ($q) => $q->where('user_id', $user->id))
            ->whereIn('idempotency_key', $keys)
            ->exists();
    }

    public function hasActiveCreatorReserve(Market $market): bool
    {
        $market->loadMissing('creator');

        if (! $this->hasStakeLockForMarket($market->creator, $market, 'creator')) {
            return false;
        }

        return ! LedgerEntry::query()
            ->whereHas('wallet', fn ($q) => $q->where('user_id', $market->creator_id))
            ->whereIn('idempotency_key', [
                $this->stakeReleaseKey($market, $market->creator),
                'stake_release:market:'.$market->id.':creator',
                'void_refund:market:'.$market->id.':user:'.$market->creator_id,
                'settle_debit:market:'.$market->id.':user:'.$market->creator_id,
                'settle_credit:market:'.$market->id.':user:'.$market->creator_id,
            ])
            ->exists();
    }

    public function creditAvailable(
        User $user,
        float $amount,
        LedgerEntryType $type,
        string $idempotencyKey,
        ?string $referenceType = null,
        ?int $referenceId = null,
        array $metadata = []
    ): Wallet {
        if ($amount <= 0) {
            throw new RuntimeException('Credit amount must be positive.');
        }

        return DB::transaction(function () use ($user, $amount, $type, $idempotencyKey, $referenceType, $referenceId, $metadata) {
            if (LedgerEntry::where('idempotency_key', $idempotencyKey)->exists()) {
                return $this->getOrCreateWallet($user);
            }

            $wallet = $this->lockWalletForUser($user);
            $wallet->available = bcadd((string) $wallet->available, (string) $amount, 2);
            $wallet->save();

            $this->recordEntry($wallet, $type, $amount, $idempotencyKey, $referenceType, $referenceId, $metadata);

            return $wallet->fresh();
        });
    }

    public function debitAvailable(
        User $user,
        float $amount,
        LedgerEntryType $type,
        string $idempotencyKey,
        ?string $referenceType = null,
        ?int $referenceId = null,
        array $metadata = []
    ): Wallet {
        if ($amount <= 0) {
            throw new RuntimeException('Debit amount must be positive.');
        }

        return DB::transaction(function () use ($user, $amount, $type, $idempotencyKey, $referenceType, $referenceId, $metadata) {
            if (LedgerEntry::where('idempotency_key', $idempotencyKey)->exists()) {
                return $this->getOrCreateWallet($user);
            }

            $wallet = $this->lockWalletForUser($user);

            if (bccomp((string) $wallet->available, (string) $amount, 2) < 0) {
                throw new RuntimeException('Insufficient available balance.');
            }

            $wallet->available = bcsub((string) $wallet->available, (string) $amount, 2);
            $wallet->save();

            $this->recordEntry($wallet, $type, $amount, $idempotencyKey, $referenceType, $referenceId, $metadata);

            return $wallet->fresh();
        });
    }

    /**
     * Last ledger snapshot for reconciliation against wallet row.
     *
     * @return array{available: float, locked: float}|null
     */
    public function ledgerSnapshotBalances(Wallet $wallet): ?array
    {
        $entry = LedgerEntry::where('wallet_id', $wallet->id)->orderByDesc('id')->first();

        if (! $entry) {
            return ['available' => 0.0, 'locked' => 0.0];
        }

        return [
            'available' => (float) $entry->balance_after_available,
            'locked' => (float) $entry->balance_after_locked,
        ];
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
