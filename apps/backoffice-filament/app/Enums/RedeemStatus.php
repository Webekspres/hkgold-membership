<?php

declare(strict_types=1);

namespace App\Enums;

enum RedeemStatus: string
{
    case Completed = 'COMPLETED';
    case Refunded = 'REFUNDED';

    public function label(): string
    {
        return match ($this) {
            self::Completed => 'Selesai',
            self::Refunded => 'Refund',
        };
    }
}
