<?php

namespace App\Console\Commands;

use App\Betting\Enums\MarketStatus;
use App\Betting\Models\Market;
use App\Betting\Services\SettlementService;
use Illuminate\Console\Command;

class FinalizeDisputeWindows extends Command
{
    protected $signature = 'betting:finalize-disputes';

    protected $description = 'Settle markets whose dispute windows have ended without open disputes';

    public function handle(SettlementService $settlementService): int
    {
        $count = 0;
        $failed = 0;

        Market::query()
            ->where('status', MarketStatus::DisputeWindow)
            ->whereNotNull('dispute_window_ends_at')
            ->where('dispute_window_ends_at', '<=', now())
            ->each(function (Market $market) use ($settlementService, &$count, &$failed) {
                try {
                    $settlementService->finalizeAfterDisputeWindow($market);
                    $count++;
                } catch (\Throwable $e) {
                    $failed++;
                    report($e);
                }
            });

        $this->info("Finalized {$count} markets. {$failed} failed.");

        return self::SUCCESS;
    }
}
