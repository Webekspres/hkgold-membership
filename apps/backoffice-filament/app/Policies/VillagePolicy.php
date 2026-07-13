<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Village;
use Illuminate\Auth\Access\HandlesAuthorization;

class VillagePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Village');
    }

    public function view(AuthUser $authUser, Village $village): bool
    {
        return $authUser->can('View:Village');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Village');
    }

    public function update(AuthUser $authUser, Village $village): bool
    {
        return $authUser->can('Update:Village');
    }

    public function delete(AuthUser $authUser, Village $village): bool
    {
        return $authUser->can('Delete:Village');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Village');
    }

    public function restore(AuthUser $authUser, Village $village): bool
    {
        return $authUser->can('Restore:Village');
    }

    public function forceDelete(AuthUser $authUser, Village $village): bool
    {
        return $authUser->can('ForceDelete:Village');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Village');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Village');
    }

    public function replicate(AuthUser $authUser, Village $village): bool
    {
        return $authUser->can('Replicate:Village');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Village');
    }

}