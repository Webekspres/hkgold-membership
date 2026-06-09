<?php

declare(strict_types=1);

namespace App\Enums;

enum MutationType: string
{
    case Earn = 'EARN';
    case Redeem = 'REDEEM';
    case Adjustment = 'ADJUSTMENT';
    case Expired = 'EXPIRED';
}
