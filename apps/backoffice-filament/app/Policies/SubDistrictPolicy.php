<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\SubDistrict;
use Illuminate\Auth\Access\HandlesAuthorization;

class SubDistrictPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SubDistrict');
    }

    public function view(AuthUser $authUser, SubDistrict $subDistrict): bool
    {
        return $authUser->can('View:SubDistrict');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SubDistrict');
    }

    public function update(AuthUser $authUser, SubDistrict $subDistrict): bool
    {
        return $authUser->can('Update:SubDistrict');
    }

    public function delete(AuthUser $authUser, SubDistrict $subDistrict): bool
    {
        return $authUser->can('Delete:SubDistrict');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:SubDistrict');
    }

    public function restore(AuthUser $authUser, SubDistrict $subDistrict): bool
    {
        return $authUser->can('Restore:SubDistrict');
    }

    public function forceDelete(AuthUser $authUser, SubDistrict $subDistrict): bool
    {
        return $authUser->can('ForceDelete:SubDistrict');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SubDistrict');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SubDistrict');
    }

    public function replicate(AuthUser $authUser, SubDistrict $subDistrict): bool
    {
        return $authUser->can('Replicate:SubDistrict');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SubDistrict');
    }

}