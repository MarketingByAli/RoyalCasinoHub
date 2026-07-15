<?php

namespace App\Console\Commands;

use App\Betting\Enums\MarketStatus;
use App\Betting\Models\Market;
use App\Betting\Services\MarketStateMachine;
use Illuminate\Console\Command;

class ExpireOpenMarkets extends Command
{
    protected $signature = 'betting:expire-markets';

    protected $description = 'Expire open challenges past their invitation expiry';

    public function handle(MarketStateMachine $stateMachine): int
    {
        $count = 0;

        Market::query()
            ->where('status', MarketStatus::Open)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->each(function (Market $market) use ($stateMachine, &$count) {
                $stateMachine->transition($market, MarketStatus::Expired, null, 'invite_expired');
                $count++;
            });

        $this->info("Expired {$count} open markets.");

        return self::SUCCESS;
    }
}
