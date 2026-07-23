<?php

declare(strict_types=1);

namespace App\Enums;

enum InjectionStatus: string
{
    case Pending = 'PENDING';
    case Validated = 'VALIDATED';
    case Failed = 'FAILED';
    case Success = 'SUCCESS';
}
