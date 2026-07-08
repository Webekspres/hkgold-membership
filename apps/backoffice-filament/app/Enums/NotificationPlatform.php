<?php

declare(strict_types=1);

namespace App\Enums;

enum NotificationPlatform: string
{
    case WebBrowserPush = 'WEB_BROWSER_PUSH';
    case MobileAppPush = 'MOBILE_APP_PUSH';
    case WebAdminInApp = 'WEB_ADMIN_IN_APP';
}
