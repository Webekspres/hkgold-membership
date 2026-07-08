<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Content;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContentPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Content');
    }

    public function view(AuthUser $authUser, Content $content): bool
    {
        return $authUser->can('View:Content');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Content');
    }

    public function update(AuthUser $authUser, Content $content): bool
    {
        return $authUser->can('Update:Content');
    }

    public function delete(AuthUser $authUser, Content $content): bool
    {
        return $authUser->can('Delete:Content');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Content');
    }

    public function restore(AuthUser $authUser, Content $content): bool
    {
        return $authUser->can('Restore:Content');
    }

    public function forceDelete(AuthUser $authUser, Content $content): bool
    {
        return $authUser->can('ForceDelete:Content');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Content');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Content');
    }

    public function replicate(AuthUser $authUser, Content $content): bool
    {
        return $authUser->can('Replicate:Content');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Content');
    }

}