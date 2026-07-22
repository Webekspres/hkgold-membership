<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PhoneApproval;
use Illuminate\Auth\Access\HandlesAuthorization;

class PhoneApprovalPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PhoneApproval');
    }

    public function view(AuthUser $authUser, PhoneApproval $phoneApproval): bool
    {
        return $authUser->can('View:PhoneApproval');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PhoneApproval');
    }

    public function update(AuthUser $authUser, PhoneApproval $phoneApproval): bool
    {
        return $authUser->can('Update:PhoneApproval');
    }

    public function delete(AuthUser $authUser, PhoneApproval $phoneApproval): bool
    {
        return $authUser->can('Delete:PhoneApproval');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PhoneApproval');
    }

    public function restore(AuthUser $authUser, PhoneApproval $phoneApproval): bool
    {
        return $authUser->can('Restore:PhoneApproval');
    }

    public function forceDelete(AuthUser $authUser, PhoneApproval $phoneApproval): bool
    {
        return $authUser->can('ForceDelete:PhoneApproval');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PhoneApproval');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PhoneApproval');
    }

    public function replicate(AuthUser $authUser, PhoneApproval $phoneApproval): bool
    {
        return $authUser->can('Replicate:PhoneApproval');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PhoneApproval');
    }

}