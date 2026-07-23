<?php

declare(strict_types=1);

namespace App\Enums;

enum Role: string
{
    // case SuperAdmin = 'SUPER_ADMIN';
    // case Executive = 'EXECUTIVE';
    // case StoreManager = 'STORE_MANAGER';
    // case Marketing = 'MARKETING';
    // case Member = 'MEMBER';

    // administrator
    // superadmin
    // marketing
    // store manager
    // member

    case Administrator = 'ADMINISTRATOR';
    case SuperAdmin = 'SUPER_ADMIN';
    case Marketing = 'MARKETING';
    case StoreManager = 'STORE_MANAGER';
    case Member = 'MEMBER';
}
