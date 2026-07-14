<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\NotificationCampaign;
use Illuminate\Auth\Access\HandlesAuthorization;

class NotificationCampaignPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:NotificationCampaign');
    }

    public function view(AuthUser $authUser, NotificationCampaign $notificationCampaign): bool
    {
        return $authUser->can('View:NotificationCampaign');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:NotificationCampaign');
    }

    public function update(AuthUser $authUser, NotificationCampaign $notificationCampaign): bool
    {
        return $authUser->can('Update:NotificationCampaign');
    }

    public function delete(AuthUser $authUser, NotificationCampaign $notificationCampaign): bool
    {
        return $authUser->can('Delete:NotificationCampaign');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:NotificationCampaign');
    }

    public function restore(AuthUser $authUser, NotificationCampaign $notificationCampaign): bool
    {
        return $authUser->can('Restore:NotificationCampaign');
    }

    public function forceDelete(AuthUser $authUser, NotificationCampaign $notificationCampaign): bool
    {
        return $authUser->can('ForceDelete:NotificationCampaign');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:NotificationCampaign');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:NotificationCampaign');
    }

    public function replicate(AuthUser $authUser, NotificationCampaign $notificationCampaign): bool
    {
        return $authUser->can('Replicate:NotificationCampaign');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:NotificationCampaign');
    }

}