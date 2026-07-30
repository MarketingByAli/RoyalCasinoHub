<?php

namespace App\Betting\Services;

use App\Betting\Enums\LedgerEntryType;
use App\Betting\Models\DepositMethod;
use App\Betting\Models\DepositNotice;
use App\Betting\Models\WithdrawRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WalletFundingService
{
    public function __construct(
        private PlayWalletService $walletService,
        private BettingNotificationService $notifications,
    ) {}

    public function submitWithdrawRequest(User $user, array $data): WithdrawRequest
    {
        $amount = (float) $data['amount'];
        if ($amount <= 0) {
            throw new RuntimeException('Withdrawal amount must be positive.');
        }

        $method = null;
        if (! empty($data['deposit_method_id'])) {
            $method = DepositMethod::where('is_active', true)->findOrFail($data['deposit_method_id']);
        }

        return DB::transaction(function () use ($user, $data, $amount, $method) {
            $request = WithdrawRequest::create([
                'user_id' => $user->id,
                'deposit_method_id' => $method?->id,
                'coin_name' => $method?->coin_name ?? $data['coin_name'],
                'network' => $method?->network ?? ($data['network'] ?? null),
                'destination_address' => $data['destination_address'],
                'amount' => $amount,
                'status' => 'pending',
                'user_note' => $data['user_note'] ?? null,
            ]);

            $this->walletService->lockStake(
                $user,
                $amount,
                WithdrawRequest::class,
                $request->id,
                'withdrawal_hold:'.$request->id
            );

            return $request;
        });
    }

    public function approveWithdraw(WithdrawRequest $request, User $admin, ?string $adminNote = null): WithdrawRequest
    {
        if ($request->status !== 'pending') {
            throw new RuntimeException('This withdrawal is not pending.');
        }

        return DB::transaction(function () use ($request, $admin, $adminNote) {
            $request = WithdrawRequest::where('id', $request->id)->lockForUpdate()->firstOrFail();
            if ($request->status !== 'pending') {
                throw new RuntimeException('This withdrawal is not pending.');
            }

            $user = $request->user;
            $amount = (float) $request->amount;

            // Release hold then permanently debit (net: remove from locked).
            $this->walletService->releaseStake(
                $user,
                $amount,
                WithdrawRequest::class,
                $request->id,
                'withdrawal_release:'.$request->id
            );

            $this->walletService->debitAvailable(
                $user,
                $amount,
                LedgerEntryType::Withdrawal,
                'withdrawal:'.$request->id,
                WithdrawRequest::class,
                $request->id,
                ['admin_id' => $admin->id]
            );

            $request->status = 'paid';
            $request->admin_note = $adminNote;
            $request->reviewed_by = $admin->id;
            $request->reviewed_at = now();
            $request->save();

            $this->notifications->notify($user, 'withdrawal_paid', [
                'amount' => $amount,
                'coin' => $request->coin_name,
            ]);

            return $request->fresh();
        });
    }

    public function rejectWithdraw(WithdrawRequest $request, User $admin, ?string $adminNote = null): WithdrawRequest
    {
        if ($request->status !== 'pending') {
            throw new RuntimeException('This withdrawal is not pending.');
        }

        return DB::transaction(function () use ($request, $admin, $adminNote) {
            $request = WithdrawRequest::where('id', $request->id)->lockForUpdate()->firstOrFail();
            if ($request->status !== 'pending') {
                throw new RuntimeException('This withdrawal is not pending.');
            }

            $this->walletService->releaseStake(
                $request->user,
                (float) $request->amount,
                WithdrawRequest::class,
                $request->id,
                'withdrawal_release:'.$request->id
            );

            $request->status = 'rejected';
            $request->admin_note = $adminNote;
            $request->reviewed_by = $admin->id;
            $request->reviewed_at = now();
            $request->save();

            $this->notifications->notify($request->user, 'withdrawal_rejected', [
                'amount' => (float) $request->amount,
            ]);

            return $request->fresh();
        });
    }

    public function submitDepositNotice(User $user, array $data): DepositNotice
    {
        return DepositNotice::create([
            'user_id' => $user->id,
            'deposit_method_id' => $data['deposit_method_id'] ?? null,
            'amount' => $data['amount'] ?? null,
            'tx_hash' => $data['tx_hash'] ?? null,
            'user_note' => $data['user_note'] ?? null,
            'status' => 'pending',
        ]);
    }

    public function creditDepositNotice(DepositNotice $notice, User $admin, float $amount, ?string $adminNote = null): DepositNotice
    {
        if ($notice->status !== 'pending') {
            throw new RuntimeException('This deposit notice is not pending.');
        }

        if ($amount <= 0) {
            throw new RuntimeException('Credit amount must be positive.');
        }

        return DB::transaction(function () use ($notice, $admin, $amount, $adminNote) {
            $notice = DepositNotice::where('id', $notice->id)->lockForUpdate()->firstOrFail();
            if ($notice->status !== 'pending') {
                throw new RuntimeException('This deposit notice is not pending.');
            }

            $this->walletService->creditAvailable(
                $notice->user,
                $amount,
                LedgerEntryType::Deposit,
                'deposit:notice:'.$notice->id,
                DepositNotice::class,
                $notice->id,
                ['admin_id' => $admin->id]
            );

            $notice->status = 'credited';
            $notice->credited_amount = $amount;
            $notice->admin_note = $adminNote;
            $notice->reviewed_by = $admin->id;
            $notice->reviewed_at = now();
            $notice->save();

            $this->notifications->notify($notice->user, 'deposit_credited', [
                'amount' => $amount,
            ]);

            return $notice->fresh();
        });
    }

    public function rejectDepositNotice(DepositNotice $notice, User $admin, ?string $adminNote = null): DepositNotice
    {
        if ($notice->status !== 'pending') {
            throw new RuntimeException('This deposit notice is not pending.');
        }

        $notice->status = 'rejected';
        $notice->admin_note = $adminNote;
        $notice->reviewed_by = $admin->id;
        $notice->reviewed_at = now();
        $notice->save();

        return $notice;
    }
}
