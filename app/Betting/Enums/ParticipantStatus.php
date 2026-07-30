<?php

namespace App\Betting\Enums;

enum ParticipantStatus: string
{
    case PendingCounter = 'pending_counter';
    case Active = 'active';
    case Withdrawn = 'withdrawn';
}
