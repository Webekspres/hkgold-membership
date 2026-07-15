<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Role;
use BezhanSalleh\FilamentShield\Support\Utils;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\PermissionRegistrar;

class ShieldRolesSeeder extends Seeder
{
    public function run(): void
    {
        Utils::createRole(name: Utils::getSuperAdminName());
        Utils::createRole(name: strtolower(Role::SuperAdmin->value));

        foreach (Role::cases() as $role) {
            if (in_array($role, [Role::SuperAdmin, Role::Administrator], true)) {
                continue;
            }

            Utils::createRole(name: strtolower($role->value));
        }

        $this->ensureShieldPermissionsGenerated();
        $this->ensureActivityLogPermissionsGenerated();
        $this->ensureNotificationCampaignPermissionsGenerated();
        $this->ensureCustomPermissions();
        $this->removePanelUserRole();

        $this->syncCmsPermissionsForRole(strtolower(Role::Administrator->value), fullAccess: true);
        $this->syncCmsPermissionsForRole(strtolower(Role::Marketing->value), fullAccess: false);
        $this->syncCmsPermissionsForRole(strtolower(Role::StoreManager->value), fullAccess: false);

        $readOnlyGroupResources = array_merge(
            $this->katalogRewardResources(),
            $this->loyaltyPointResources(),
            $this->redeemPoinResources(),
        );

        $this->syncResourceGroupPermissionsForRole(
            strtolower(Role::SuperAdmin->value),
            $readOnlyGroupResources,
            fullAccess: true,
        );

        foreach ([Role::Marketing, Role::StoreManager] as $role) {
            $this->syncResourceGroupPermissionsForRole(
                strtolower($role->value),
                $readOnlyGroupResources,
                fullAccess: false,
            );
        }

        foreach ([Role::SuperAdmin, Role::Administrator, Role::StoreManager] as $role) {
            $this->syncResourceGroupPermissionsForRole(
                strtolower($role->value),
                $this->redeemTokenResources(),
                fullAccess: true,
            );
        }

        $this->syncSuperAdminPermissions();
        $this->syncMemberLookupPermissionsForRole(strtolower(Role::Marketing->value));
        $this->syncMemberLookupPermissionsForRole(strtolower(Role::StoreManager->value));
        $this->syncNotificationCampaignPermissions();
        $this->revokeMemberStaffPermissionsForRole(strtolower(Role::Member->value));
        $this->revokeResourceGroupPermissionsForRole(
            strtolower(Role::Member->value),
            $readOnlyGroupResources,
        );

        foreach ([Role::SuperAdmin, Role::Marketing, Role::StoreManager, Role::Member] as $role) {
            $this->revokeAdminOnlyPermissionsForRole(strtolower($role->value));
        }
    }

    private function ensureActivityLogPermissionsGenerated(): void
    {
        if (Permission::query()->where('name', 'ViewAny:ActivityLog')->exists()) {
            return;
        }

        Artisan::call('shield:generate', [
            '--all' => true,
            '--panel' => 'app',
            '--no-interaction' => true,
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function ensureShieldPermissionsGenerated(): void
    {
        if (Permission::query()->where('name', 'ViewAny:Content')->exists()) {
            return;
        }

        Artisan::call('shield:generate', [
            '--all' => true,
            '--panel' => 'app',
            '--no-interaction' => true,
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function ensureNotificationCampaignPermissionsGenerated(): void
    {
        if (Permission::query()->where('name', 'ViewAny:NotificationCampaign')->exists()) {
            return;
        }

        Artisan::call('shield:generate', [
            '--all' => true,
            '--panel' => 'app',
            '--no-interaction' => true,
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function ensureCustomPermissions(): void
    {
        foreach ([
            'Update:PromotionBannerPage',
            'View:MemberLookupPage',
            'Create:BroadcastNotification',
        ] as $permissionName) {
            Permission::query()->firstOrCreate([
                'name' => $permissionName,
                'guard_name' => config('auth.defaults.guard', 'web'),
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function removePanelUserRole(): void
    {
        $roleName = Utils::getPanelUserRoleName();

        $role = SpatieRole::query()->where('name', $roleName)->first();

        if ($role === null) {
            return;
        }

        $role->users()->detach();
        $role->permissions()->detach();
        $role->delete();
    }

    private function syncSuperAdminPermissions(): void
    {
        $role = SpatieRole::query()
            ->where('name', strtolower(Role::SuperAdmin->value))
            ->first();

        if ($role === null) {
            return;
        }

        $permissions = Permission::query()
            ->pluck('name')
            ->reject(fn (string $name): bool => $this->isExcludedFromSuperAdminPermission($name))
            ->values();

        if ($permissions->isEmpty()) {
            return;
        }

        $role->syncPermissions($permissions->all());
    }

    /**
     * @param  array<int, string>  $resourceNames
     */
    private function syncResourceGroupPermissionsForRole(string $roleName, array $resourceNames, bool $fullAccess): void
    {
        $role = SpatieRole::query()->where('name', $roleName)->first();

        if ($role === null) {
            return;
        }

        $groupPermissions = $this->permissionsForResources($resourceNames);

        if ($groupPermissions->isEmpty()) {
            return;
        }

        $readPermissions = $groupPermissions
            ->filter(fn (string $name): bool => $this->isReadOnlyPermission($name))
            ->values();

        $writePermissions = $groupPermissions
            ->reject(fn (string $name): bool => $this->isReadOnlyPermission($name))
            ->values();

        if ($readPermissions->isNotEmpty()) {
            $role->givePermissionTo($readPermissions->all());
        }

        if ($writePermissions->isEmpty()) {
            return;
        }

        if ($fullAccess) {
            $role->givePermissionTo($writePermissions->all());

            return;
        }

        $assignedWritePermissions = $role->permissions()
            ->whereIn('name', $writePermissions)
            ->pluck('name');

        if ($assignedWritePermissions->isNotEmpty()) {
            $role->revokePermissionTo($assignedWritePermissions->all());
        }
    }

    /**
     * @param  array<int, string>  $resourceNames
     */
    private function revokeResourceGroupPermissionsForRole(string $roleName, array $resourceNames): void
    {
        $role = SpatieRole::query()->where('name', $roleName)->first();

        if ($role === null) {
            return;
        }

        $assignedPermissions = $role->permissions()
            ->pluck('name')
            ->filter(fn (string $name): bool => $this->matchesResourcePermission($name, $resourceNames))
            ->values();

        if ($assignedPermissions->isNotEmpty()) {
            $role->revokePermissionTo($assignedPermissions->all());
        }
    }

    private function revokeAdminOnlyPermissionsForRole(string $roleName): void
    {
        $this->revokeResourceGroupPermissionsForRole($roleName, $this->adminOnlyResources());
    }

    private function syncMemberLookupPermissionsForRole(string $roleName): void
    {
        $role = SpatieRole::query()->where('name', $roleName)->first();

        if ($role === null) {
            return;
        }

        $this->revokeMemberStaffPermissionsForRole($roleName);

        $lookupPermission = Permission::query()
            ->where('name', 'View:MemberLookupPage')
            ->value('name');

        if ($lookupPermission !== null) {
            $role->givePermissionTo($lookupPermission);
        }
    }

    private function revokeMemberStaffPermissionsForRole(string $roleName): void
    {
        $role = SpatieRole::query()->where('name', $roleName)->first();

        if ($role === null) {
            return;
        }

        $assignedPermissions = $role->permissions()
            ->pluck('name')
            ->filter(fn (string $name): bool => $this->isMemberOrStaffResourcePermission($name))
            ->values();

        if ($assignedPermissions->isNotEmpty()) {
            $role->revokePermissionTo($assignedPermissions->all());
        }
    }

    private function isExcludedFromSuperAdminPermission(string $name): bool
    {
        return $this->isMemberOrStaffResourcePermission($name)
            || $name === 'View:MemberLookupPage'
            || $this->isAdminOnlyResourcePermission($name);
    }

    private function isMemberOrStaffResourcePermission(string $name): bool
    {
        if ($name === 'View:MemberLookupPage') {
            return false;
        }

        return (bool) preg_match('/:(Member|Staff)(?:$|:)/', $name);
    }

    private function isAdminOnlyResourcePermission(string $name): bool
    {
        return $this->matchesResourcePermission($name, $this->adminOnlyResources());
    }

    private function isReadOnlyPermission(string $name): bool
    {
        return preg_match('/^(ViewAny|View):/', $name) === 1;
    }

    /**
     * @param  array<int, string>  $resourceNames
     * @return Collection<int, string>
     */
    private function permissionsForResources(array $resourceNames): Collection
    {
        return Permission::query()
            ->pluck('name')
            ->filter(fn (string $name): bool => $this->matchesResourcePermission($name, $resourceNames))
            ->values();
    }

    /**
     * @param  array<int, string>  $resourceNames
     */
    private function matchesResourcePermission(string $permissionName, array $resourceNames): bool
    {
        foreach ($resourceNames as $resourceName) {
            if (preg_match('/^[^:]+:'.preg_quote($resourceName, '/').'$/', $permissionName) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, string>
     */
    private function katalogRewardResources(): array
    {
        return [
            'CategoryReward',
            'Reward',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function loyaltyPointResources(): array
    {
        return [
            'PointInjectionBatch',
            'PointMutation',
            'PointAnnualArchivePeriod',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function redeemPoinResources(): array
    {
        return [
            'RedeemInvoice',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function redeemTokenResources(): array
    {
        return [
            'RedeemToken',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function adminOnlyResources(): array
    {
        return [
            'Branch',
            'TierMember',
            'Province',
            'City',
            'SubDistrict',
            'Village',
            'PostalCode',
            'Role',
            'ActivityLog',
        ];
    }

    private function syncCmsPermissionsForRole(string $roleName, bool $fullAccess): void
    {
        $role = SpatieRole::query()->where('name', $roleName)->first();

        if ($role === null) {
            return;
        }

        $readPermissions = Permission::query()
            ->whereIn('name', array_merge(
                $this->contentReadPermissions(),
                $this->promotionBannerReadPermissions(),
            ))
            ->pluck('name');

        $writePermissions = Permission::query()
            ->whereIn('name', array_merge(
                $this->contentWritePermissions(),
                $this->promotionBannerWritePermissions(),
            ))
            ->pluck('name');

        if ($readPermissions->isNotEmpty()) {
            $role->givePermissionTo($readPermissions->all());
        }

        if ($writePermissions->isEmpty()) {
            return;
        }

        if ($fullAccess) {
            $role->givePermissionTo($writePermissions->all());

            return;
        }

        $assignedWritePermissions = $role->permissions()
            ->whereIn('name', $writePermissions)
            ->pluck('name');

        if ($assignedWritePermissions->isNotEmpty()) {
            $role->revokePermissionTo($assignedWritePermissions->all());
        }
    }

    /**
     * @return array<int, string>
     */
    private function contentReadPermissions(): array
    {
        return [
            'ViewAny:Content',
            'View:Content',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function contentWritePermissions(): array
    {
        return [
            'Create:Content',
            'Update:Content',
            'Delete:Content',
            'DeleteAny:Content',
            'ForceDelete:Content',
            'ForceDeleteAny:Content',
            'Restore:Content',
            'RestoreAny:Content',
            'Replicate:Content',
            'Reorder:Content',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function promotionBannerReadPermissions(): array
    {
        return [
            'View:PromotionBannerPage',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function promotionBannerWritePermissions(): array
    {
        return [
            'Update:PromotionBannerPage',
        ];
    }

    private function syncNotificationCampaignPermissions(): void
    {
        $readPermissions = Permission::query()
            ->whereIn('name', $this->notificationCampaignReadPermissions())
            ->pluck('name');

        $broadcastPermission = Permission::query()
            ->where('name', 'Create:BroadcastNotification')
            ->value('name');

        $marketingRole = SpatieRole::query()
            ->where('name', strtolower(Role::Marketing->value))
            ->first();

        if ($marketingRole !== null) {
            if ($readPermissions->isNotEmpty()) {
                $marketingRole->givePermissionTo($readPermissions->all());
            }

            if ($broadcastPermission !== null) {
                $marketingRole->givePermissionTo($broadcastPermission);
            }
        }

        $storeManagerRole = SpatieRole::query()
            ->where('name', strtolower(Role::StoreManager->value))
            ->first();

        if ($storeManagerRole !== null && $readPermissions->isNotEmpty()) {
            $storeManagerRole->givePermissionTo($readPermissions->all());
        }

        if ($broadcastPermission !== null && $storeManagerRole !== null) {
            if ($storeManagerRole->hasPermissionTo($broadcastPermission)) {
                $storeManagerRole->revokePermissionTo($broadcastPermission);
            }
        }
    }

    /**
     * @return array<int, string>
     */
    private function notificationCampaignReadPermissions(): array
    {
        return [
            'ViewAny:NotificationCampaign',
            'View:NotificationCampaign',
        ];
    }
}
