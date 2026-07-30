<?php

namespace App\Betting\Services;

use App\Betting\Enums\LedgerEntryType;
use App\Betting\Enums\MarketStatus;
use App\Betting\Enums\ParticipantStatus;
use App\Betting\Models\LedgerEntry;
use App\Betting\Models\Market;
use App\Betting\Models\Wallet;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SettlementReversalService
{
    public function __construct(
        private MarketStateMachine $stateMachine,
        private PlayWalletService $walletService,
        private BettingAuditService $audit,
        private SettlementService $settlementService,
    ) {}

    public function reverseSettlement(Market $market, User $admin, string $reason): Market
    {
        if ($market->status !== MarketStatus::Settled) {
            throw new RuntimeException('Only settled markets can be reversed.');
        }

        return DB::transaction(function () use ($market, $admin, $reason) {
            $market = Market::where('id', $market->id)->lockForUpdate()->firstOrFail();

            if ($market->status !== MarketStatus::Settled) {
                throw new RuntimeException('Only settled markets can be reversed.');
            }

            $participants = $market->participants()
                ->with('user')
                ->where('status', ParticipantStatus::Active)
                ->get();

            foreach ($participants as $participant) {
                $creditKey = 'settle_credit:market:'.$market->id.':user:'.$participant->user_id;
                $debitKey = 'settle_debit:market:'.$market->id.':user:'.$participant->user_id;

                $credit = LedgerEntry::where('idempotency_key', $creditKey)->first();
                $debit = LedgerEntry::where('idempotency_key', $debitKey)->first();

                if ($credit) {
                    $this->walletService->debitAvailable(
                        $participant->user,
                        (float) $credit->amount,
                        LedgerEntryType::SettlementReversalDebit,
                        'settlement_reversal_debit:market:'.$market->id.':user:'.$participant->user_id,
                        Market::class,
                        $market->id
                    );
                    // Relock stake that was released on win.
                    $this->walletService->lockStake(
                        $participant->user,
                        (float) $participant->stake_amount,
                        Market::class,
                        $market->id,
                        'stake_lock:market:'.$market->id.':user:'.$participant->user_id.':reversal'
                    );
                }

                if ($debit) {
                    // Loser had locked stake removed; restore locked stake.
                    $wallet = Wallet::where('user_id', $participant->user_id)->lockForUpdate()->firstOrFail();
                    $wallet->locked = bcadd((string) $wallet->locked, (string) $participant->stake_amount, 2);
                    $wallet->save();

                    LedgerEntry::create([
                        'wallet_id' => $wallet->id,
                        'type' => LedgerEntryType::SettlementReversalCredit,
                        'amount' => $participant->stake_amount,
                        'balance_after_available' => $wallet->available,
                        'balance_after_locked' => $wallet->locked,
                        'reference_type' => Market::class,
                        'reference_id' => $market->id,
                        'idempotency_key' => 'settlement_reversal_credit:market:'.$market->id.':user:'.$participant->user_id,
                        'metadata' => ['reason' => $reason],
                    ]);
                }
            }

            $feeEntry = LedgerEntry::where('idempotency_key', 'fee:market:'.$market->id)->first();
            if ($feeEntry && config('betting.house_user_id')) {
                $house = User::find(config('betting.house_user_id'));
                if ($house) {
                    $this->walletService->debitAvailable(
                        $house,
                        (float) $feeEntry->amount,
                        LedgerEntryType::SettlementReversalDebit,
                        'settlement_reversal_fee:market:'.$market->id,
                        Market::class,
                        $market->id
                    );
                }
            }

            // Move back to under_dispute so admin can re-settle or void.
            $market->status = MarketStatus::UnderDispute;
            $market->save();

            $this->audit->log($market, MarketStatus::Settled->value, MarketStatus::UnderDispute->value, $admin, $reason, [], 'admin');

            return $market->fresh();
        });
    }

    public function reverseAndVoid(Market $market, User $admin, string $reason): Market
    {
        $market = $this->reverseSettlement($market, $admin, $reason);

        return $this->settlementService->voidMarket($market, $admin, 'reversal_void:'.$reason);
    }
}
