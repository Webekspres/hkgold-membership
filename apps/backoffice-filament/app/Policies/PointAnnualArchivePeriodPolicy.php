<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Role;
use App\Models\PointAnnualArchivePeriod;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PointAnnualArchivePeriodPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PointAnnualArchivePeriod');
    }

    public function view(AuthUser $authUser, PointAnnualArchivePeriod $pointAnnualArchivePeriod): bool
    {
        return $authUser->can('View:PointAnnualArchivePeriod');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PointAnnualArchivePeriod')
            && $authUser->role === Role::Administrator;
    }

    public function update(AuthUser $authUser, PointAnnualArchivePeriod $pointAnnualArchivePeriod): bool
    {
        return $authUser->can('Update:PointAnnualArchivePeriod');
    }

    public function delete(AuthUser $authUser, PointAnnualArchivePeriod $pointAnnualArchivePeriod): bool
    {
        return $authUser->can('Delete:PointAnnualArchivePeriod');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PointAnnualArchivePeriod');
    }

    public function restore(AuthUser $authUser, PointAnnualArchivePeriod $pointAnnualArchivePeriod): bool
    {
        return $authUser->can('Restore:PointAnnualArchivePeriod');
    }

    public function forceDelete(AuthUser $authUser, PointAnnualArchivePeriod $pointAnnualArchivePeriod): bool
    {
        return $authUser->can('ForceDelete:PointAnnualArchivePeriod');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PointAnnualArchivePeriod');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PointAnnualArchivePeriod');
    }

    public function replicate(AuthUser $authUser, PointAnnualArchivePeriod $pointAnnualArchivePeriod): bool
    {
        return $authUser->can('Replicate:PointAnnualArchivePeriod');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PointAnnualArchivePeriod');
    }
}
