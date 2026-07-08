<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RedeemInvoice;
use Illuminate\Auth\Access\HandlesAuthorization;

class RedeemInvoicePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RedeemInvoice');
    }

    public function view(AuthUser $authUser, RedeemInvoice $redeemInvoice): bool
    {
        return $authUser->can('View:RedeemInvoice');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RedeemInvoice');
    }

    public function update(AuthUser $authUser, RedeemInvoice $redeemInvoice): bool
    {
        return $authUser->can('Update:RedeemInvoice');
    }

    public function delete(AuthUser $authUser, RedeemInvoice $redeemInvoice): bool
    {
        return $authUser->can('Delete:RedeemInvoice');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:RedeemInvoice');
    }

    public function restore(AuthUser $authUser, RedeemInvoice $redeemInvoice): bool
    {
        return $authUser->can('Restore:RedeemInvoice');
    }

    public function forceDelete(AuthUser $authUser, RedeemInvoice $redeemInvoice): bool
    {
        return $authUser->can('ForceDelete:RedeemInvoice');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RedeemInvoice');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RedeemInvoice');
    }

    public function replicate(AuthUser $authUser, RedeemInvoice $redeemInvoice): bool
    {
        return $authUser->can('Replicate:RedeemInvoice');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RedeemInvoice');
    }

}