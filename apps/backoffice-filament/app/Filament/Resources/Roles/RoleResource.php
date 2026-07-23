<?php

declare(strict_types=1);

namespace App\Filament\Resources\Roles;

use BezhanSalleh\FilamentShield\Resources\Roles\RoleResource as ShieldRoleResource;
use BezhanSalleh\FilamentShield\Support\Utils;
use Illuminate\Support\Facades\Auth;

class RoleResource extends ShieldRoleResource
{
    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user !== null && $user->hasRole(Utils::getSuperAdminName());
    }
}
