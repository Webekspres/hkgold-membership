<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RedeemToken;
use Illuminate\Auth\Access\HandlesAuthorization;

class RedeemTokenPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RedeemToken');
    }

    public function view(AuthUser $authUser, RedeemToken $redeemToken): bool
    {
        return $authUser->can('View:RedeemToken');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RedeemToken');
    }

    public function update(AuthUser $authUser, RedeemToken $redeemToken): bool
    {
        return $authUser->can('Update:RedeemToken');
    }

    public function delete(AuthUser $authUser, RedeemToken $redeemToken): bool
    {
        return $authUser->can('Delete:RedeemToken');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:RedeemToken');
    }

    public function restore(AuthUser $authUser, RedeemToken $redeemToken): bool
    {
        return $authUser->can('Restore:RedeemToken');
    }

    public function forceDelete(AuthUser $authUser, RedeemToken $redeemToken): bool
    {
        return $authUser->can('ForceDelete:RedeemToken');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RedeemToken');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RedeemToken');
    }

    public function replicate(AuthUser $authUser, RedeemToken $redeemToken): bool
    {
        return $authUser->can('Replicate:RedeemToken');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RedeemToken');
    }

}