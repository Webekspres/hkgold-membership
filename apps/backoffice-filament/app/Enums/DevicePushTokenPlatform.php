<?php

declare(strict_types=1);

namespace App\Enums;

enum DevicePushTokenPlatform: string
{
    case Mobile = 'MOBILE';
    case Web = 'WEB';
}
