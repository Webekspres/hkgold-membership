<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\TierMember;
use Illuminate\Auth\Access\HandlesAuthorization;

class TierMemberPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:TierMember');
    }

    public function view(AuthUser $authUser, TierMember $tierMember): bool
    {
        return $authUser->can('View:TierMember');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:TierMember');
    }

    public function update(AuthUser $authUser, TierMember $tierMember): bool
    {
        return $authUser->can('Update:TierMember');
    }

    public function delete(AuthUser $authUser, TierMember $tierMember): bool
    {
        return $authUser->can('Delete:TierMember');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:TierMember');
    }

    public function restore(AuthUser $authUser, TierMember $tierMember): bool
    {
        return $authUser->can('Restore:TierMember');
    }

    public function forceDelete(AuthUser $authUser, TierMember $tierMember): bool
    {
        return $authUser->can('ForceDelete:TierMember');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:TierMember');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:TierMember');
    }

    public function replicate(AuthUser $authUser, TierMember $tierMember): bool
    {
        return $authUser->can('Replicate:TierMember');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:TierMember');
    }

}