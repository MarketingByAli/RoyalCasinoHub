<?php

namespace App\Betting\Services;

use App\Betting\Enums\MarketStatus;
use App\Betting\Models\Market;
use App\Models\User;
use InvalidArgumentException;

class MarketStateMachine
{
    /** @var array<string, list<string>> */
    private const TRANSITIONS = [
        'draft' => ['pending_review', 'cancelled'],
        'pending_review' => ['approved', 'rejected', 'cancelled'],
        'approved' => ['open', 'cancelled'],
        'open' => ['fully_matched', 'expired', 'cancelled'],
        'fully_matched' => ['locked', 'cancelled', 'voided'],
        'locked' => ['in_progress', 'cancelled', 'voided'],
        'in_progress' => ['pending_result', 'voided'],
        'pending_result' => ['result_published', 'voided'],
        'result_published' => ['dispute_window', 'voided'],
        'dispute_window' => ['settled', 'under_dispute', 'voided'],
        'under_dispute' => ['settled', 'voided'],
    ];

    public function __construct(
        private BettingAuditService $audit
    ) {}

    public function canTransition(Market $market, MarketStatus $to): bool
    {
        $from = $market->status->value;

        return in_array($to->value, self::TRANSITIONS[$from] ?? [], true);
    }

    public function transition(
        Market $market,
        MarketStatus $to,
        ?User $actor = null,
        ?string $reason = null,
        array $metadata = []
    ): Market {
        if (! $this->canTransition($market, $to)) {
            throw new InvalidArgumentException(
                "Invalid market transition from {$market->status->value} to {$to->value}"
            );
        }

        $previous = $market->status->value;
        $market->status = $to;
        $market->save();

        $this->audit->logMarketTransition($market, $previous, $to->value, $actor, $reason, $metadata);

        return $market->fresh();
    }
}
