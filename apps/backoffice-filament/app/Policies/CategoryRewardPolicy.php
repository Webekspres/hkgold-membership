<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\CategoryReward;
use Illuminate\Auth\Access\HandlesAuthorization;

class CategoryRewardPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CategoryReward');
    }

    public function view(AuthUser $authUser, CategoryReward $categoryReward): bool
    {
        return $authUser->can('View:CategoryReward');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CategoryReward');
    }

    public function update(AuthUser $authUser, CategoryReward $categoryReward): bool
    {
        return $authUser->can('Update:CategoryReward');
    }

    public function delete(AuthUser $authUser, CategoryReward $categoryReward): bool
    {
        return $authUser->can('Delete:CategoryReward');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:CategoryReward');
    }

    public function restore(AuthUser $authUser, CategoryReward $categoryReward): bool
    {
        return $authUser->can('Restore:CategoryReward');
    }

    public function forceDelete(AuthUser $authUser, CategoryReward $categoryReward): bool
    {
        return $authUser->can('ForceDelete:CategoryReward');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CategoryReward');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CategoryReward');
    }

    public function replicate(AuthUser $authUser, CategoryReward $categoryReward): bool
    {
        return $authUser->can('Replicate:CategoryReward');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CategoryReward');
    }

}