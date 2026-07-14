<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PointInjectionBatch;
use Illuminate\Auth\Access\HandlesAuthorization;

class PointInjectionBatchPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PointInjectionBatch');
    }

    public function view(AuthUser $authUser, PointInjectionBatch $pointInjectionBatch): bool
    {
        return $authUser->can('View:PointInjectionBatch');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PointInjectionBatch');
    }

    public function update(AuthUser $authUser, PointInjectionBatch $pointInjectionBatch): bool
    {
        return $authUser->can('Update:PointInjectionBatch');
    }

    public function delete(AuthUser $authUser, PointInjectionBatch $pointInjectionBatch): bool
    {
        return $authUser->can('Delete:PointInjectionBatch');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PointInjectionBatch');
    }

    public function restore(AuthUser $authUser, PointInjectionBatch $pointInjectionBatch): bool
    {
        return $authUser->can('Restore:PointInjectionBatch');
    }

    public function forceDelete(AuthUser $authUser, PointInjectionBatch $pointInjectionBatch): bool
    {
        return $authUser->can('ForceDelete:PointInjectionBatch');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PointInjectionBatch');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PointInjectionBatch');
    }

    public function replicate(AuthUser $authUser, PointInjectionBatch $pointInjectionBatch): bool
    {
        return $authUser->can('Replicate:PointInjectionBatch');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PointInjectionBatch');
    }

}