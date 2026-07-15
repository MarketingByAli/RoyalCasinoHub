<?php

namespace App\Console\Commands;

use App\Betting\Models\BettingEvent;
use App\Betting\Services\SettlementService;
use Illuminate\Console\Command;

class AdvanceBettingEvents extends Command
{
    protected $signature = 'betting:advance-events';

    protected $description = 'Advance matched markets when events start';

    public function handle(SettlementService $settlementService): int
    {
        $count = 0;

        BettingEvent::query()
            ->where('status', 'scheduled')
            ->where('start_at', '<=', now())
            ->each(function (BettingEvent $event) use ($settlementService, &$count) {
                $event->status = 'in_progress';
                $event->save();
                $settlementService->advanceMarketsForEventStart($event);
                $count++;
            });

        $this->info("Advanced markets for {$count} events.");

        return self::SUCCESS;
    }
}
