<?php

namespace App\Console\Commands;

use App\Betting\Services\WalletReconciliationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ReconcileBettingWallets extends Command
{
    protected $signature = 'betting:reconcile-wallets';

    protected $description = 'Compare wallet balances against latest ledger snapshots';

    public function handle(WalletReconciliationService $reconciliation): int
    {
        $mismatches = $reconciliation->findMismatches();
        Cache::put('betting.wallet_mismatches', $mismatches->count(), now()->addDay());

        if ($mismatches->isEmpty()) {
            $this->info('All wallets reconcile cleanly.');

            return self::SUCCESS;
        }

        Log::warning('Betting wallet mismatches detected', ['count' => $mismatches->count(), 'rows' => $mismatches->take(20)]);
        $this->error($mismatches->count().' wallet mismatch(es) found.');

        return self::FAILURE;
    }
}
