<?php

namespace App\Betting\Enums;

enum MarketStatus: string
{
    case Draft = 'draft';
    case PendingReview = 'pending_review';
    case Approved = 'approved';
    case Open = 'open';
    case PartiallyMatched = 'partially_matched';
    case FullyMatched = 'fully_matched';
    case Locked = 'locked';
    case InProgress = 'in_progress';
    case PendingResult = 'pending_result';
    case ResultPublished = 'result_published';
    case DisputeWindow = 'dispute_window';
    case Settled = 'settled';
    case Rejected = 'rejected';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
    case Voided = 'voided';
    case UnderDispute = 'under_dispute';
    case Suspended = 'suspended';
}
