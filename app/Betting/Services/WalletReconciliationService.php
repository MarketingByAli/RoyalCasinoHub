<?php

namespace App\Betting\Services;

use App\Betting\Models\Wallet;
use Illuminate\Support\Collection;

class WalletReconciliationService
{
    public function __construct(
        private PlayWalletService $walletService,
    ) {}

    /**
     * @return Collection<int, array{wallet_id: int, user_id: int, available: float, locked: float, ledger_available: float, ledger_locked: float}>
     */
    public function findMismatches(): Collection
    {
        $mismatches = collect();

        Wallet::query()->orderBy('id')->chunkById(200, function ($wallets) use ($mismatches) {
            foreach ($wallets as $wallet) {
                $snapshot = $this->walletService->ledgerSnapshotBalances($wallet);
                if ($snapshot === null) {
                    continue;
                }

                if (
                    bccomp((string) $wallet->available, (string) $snapshot['available'], 2) !== 0
                    || bccomp((string) $wallet->locked, (string) $snapshot['locked'], 2) !== 0
                ) {
                    $mismatches->push([
                        'wallet_id' => $wallet->id,
                        'user_id' => $wallet->user_id,
                        'available' => (float) $wallet->available,
                        'locked' => (float) $wallet->locked,
                        'ledger_available' => $snapshot['available'],
                        'ledger_locked' => $snapshot['locked'],
                    ]);
                }
            }
        });

        return $mismatches;
    }
}
