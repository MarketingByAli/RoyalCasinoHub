<?php

namespace App\Console\Commands;

use App\Betting\Enums\MarketStatus;
use App\Betting\Models\Market;
use App\Betting\Services\MarketService;
use Illuminate\Console\Command;

class ExpireOpenMarkets extends Command
{
    protected $signature = 'betting:expire-markets';

    protected $description = 'Expire open challenges past their invitation expiry';

    public function handle(MarketService $marketService): int
    {
        $count = 0;

        Market::query()
            ->whereIn('status', [MarketStatus::Open, MarketStatus::PartiallyMatched])
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->each(function (Market $market) use ($marketService, &$count) {
                $marketService->expireOpenMarket($market);
                $count++;
            });

        $this->info("Expired {$count} open/partial markets.");

        return self::SUCCESS;
    }
}
