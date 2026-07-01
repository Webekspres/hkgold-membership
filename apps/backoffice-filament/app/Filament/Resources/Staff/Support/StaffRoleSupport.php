<?php

declare(strict_types=1);

namespace App\Filament\Resources\Staff\Support;

use App\Enums\Role;

class StaffRoleSupport
{
    /**
     * @return array{label: string, color: string}
     */
    private static function meta(Role $role): array
    {
        return match ($role) {
            Role::Administrator => [
                'label' => 'Administrator',
                'color' => 'primary',
            ],
            Role::SuperAdmin => [
                'label' => 'Super Admin',
                'color' => 'rose',
            ],
            Role::Marketing => [
                'label' => 'Marketing',
                'color' => 'sky',
            ],
            Role::StoreManager => [
                'label' => 'Store Manager',
                'color' => 'emerald',
            ],
            Role::Member => [
                'label' => 'Member',
                'color' => 'gray',
            ],
        };
    }

    public static function label(Role $role): string
    {
        return self::meta($role)['label'];
    }

    public static function color(Role $role): string
    {
        return self::meta($role)['color'];
    }

    /**
     * @return array<string, string>
     */
    public static function staffRoleOptions(): array
    {
        return collect([
            Role::Administrator,
            Role::SuperAdmin,
            Role::Marketing,
            Role::StoreManager,
        ])
            ->mapWithKeys(fn (Role $role): array => [$role->value => self::label($role)])
            ->all();
    }
}
