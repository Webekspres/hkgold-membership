<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PointMutation;
use Illuminate\Auth\Access\HandlesAuthorization;

class PointMutationPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PointMutation');
    }

    public function view(AuthUser $authUser, PointMutation $pointMutation): bool
    {
        return $authUser->can('View:PointMutation');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PointMutation');
    }

    public function update(AuthUser $authUser, PointMutation $pointMutation): bool
    {
        return $authUser->can('Update:PointMutation');
    }

    public function delete(AuthUser $authUser, PointMutation $pointMutation): bool
    {
        return $authUser->can('Delete:PointMutation');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PointMutation');
    }

    public function restore(AuthUser $authUser, PointMutation $pointMutation): bool
    {
        return $authUser->can('Restore:PointMutation');
    }

    public function forceDelete(AuthUser $authUser, PointMutation $pointMutation): bool
    {
        return $authUser->can('ForceDelete:PointMutation');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PointMutation');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PointMutation');
    }

    public function replicate(AuthUser $authUser, PointMutation $pointMutation): bool
    {
        return $authUser->can('Replicate:PointMutation');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PointMutation');
    }

}