<?php

declare(strict_types=1);

namespace App\Enums;

enum ChangePhoneSource: string
{
    case SelfService = 'SELF_SERVICE';
    case AdminAssisted = 'ADMIN_ASSISTED';

    public function label(): string
    {
        return match ($this) {
            self::SelfService => 'Mandiri',
            self::AdminAssisted => 'Bantuan admin',
        };
    }
}
