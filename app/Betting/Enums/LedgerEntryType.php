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
}
