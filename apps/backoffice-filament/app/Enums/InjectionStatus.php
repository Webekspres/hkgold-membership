<?php

declare(strict_types=1);

namespace App\Enums;

enum InjectionStatus: string
{
    case Pending = 'PENDING';
    case Success = 'SUCCESS';
    case Failed = 'FAILED';
}
