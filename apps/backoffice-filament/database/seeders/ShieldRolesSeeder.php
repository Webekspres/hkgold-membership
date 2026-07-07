<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Role;
use BezhanSalleh\FilamentShield\Support\Utils;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as SpatieRole;

class ShieldRolesSeeder extends Seeder
{
    public function run(): void
    {
        Utils::createRole(name: Utils::getSuperAdminName());
        Utils::createPanelUserRole();

        foreach (Role::cases() as $role) {
            if ($role === Role::SuperAdmin) {
                continue;
            }

            Utils::createRole(name: strtolower($role->value));
        }

        // Project default:
        // marketing & store_manager (branch manager) can only read Content resource.
        $this->syncContentReadOnlyPermissions([
            strtolower(Role::Marketing->value),
            strtolower(Role::StoreManager->value),
        ]);
    }

    /**
     * @param  array<int, string>  $roleNames
     */
    private function syncContentReadOnlyPermissions(array $roleNames): void
    {
        $contentPermissions = Permission::query()
            ->get(['name'])
            ->pluck('name')
            ->filter(fn (string $name): bool => str_contains(strtolower($name), 'content'))
            ->values();

        if ($contentPermissions->isEmpty()) {
            return;
        }

        $readOnlyPermissions = $contentPermissions
            ->filter(fn (string $name): bool => preg_match('/^(viewany|view)([:._-]|$)/i', $name) === 1)
            ->values();

        $writePermissions = $contentPermissions
            ->diff($readOnlyPermissions)
            ->values();

        foreach ($roleNames as $roleName) {
            $role = SpatieRole::query()->where('name', $roleName)->first();

            if ($role === null) {
                continue;
            }

            if ($writePermissions->isNotEmpty()) {
                $role->revokePermissionTo($writePermissions->all());
            }

            if ($readOnlyPermissions->isNotEmpty()) {
                $role->givePermissionTo($readOnlyPermissions->all());
            }
        }
    }
}
