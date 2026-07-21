<?php

declare(strict_types=1);

namespace App\Enums;

enum TierStatus: string
{
    case Silver = 'SILVER';
    case Gold = 'GOLD';
    case Platinum = 'PLATINUM';
    case Elite = 'ELITE';
}
