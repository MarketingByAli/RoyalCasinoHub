<?php

namespace App\Betting\Enums;

enum AccountState: string
{
    case Unverified = 'unverified';
    case VerificationPending = 'verification_pending';
    case Verified = 'verified';
    case PlayOnly = 'play_only';
    case EnhancedVerificationRequired = 'enhanced_verification_required';
    case DepositRestricted = 'deposit_restricted';
    case BettingRestricted = 'betting_restricted';
    case WithdrawalRestricted = 'withdrawal_restricted';
    case TemporarilySuspended = 'temporarily_suspended';
    case SelfExcluded = 'self_excluded';
    case PermanentlyClosed = 'permanently_closed';
}
