<?php

namespace App\Betting\Enums;

enum MarketVisibility: string
{
    case PrivateInvite = 'private_invite';
    case Public = 'public';
}
