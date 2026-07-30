<?php

namespace App\Betting\Enums;

enum LedgerEntryType: string
{
    case Grant = 'grant';
    case StakeLock = 'stake_lock';
    case StakeRelease = 'stake_release';
    case SettlementCredit = 'settlement_credit';
    case SettlementDebit = 'settlement_debit';
    case VoidRefund = 'void_refund';
    case ManualAdjustment = 'manual_adjustment';
    case Faucet = 'faucet';
    case ReferralBonus = 'referral_bonus';
    case PlatformFee = 'platform_fee';
    case SettlementReversalCredit = 'settlement_reversal_credit';
    case SettlementReversalDebit = 'settlement_reversal_debit';
}
