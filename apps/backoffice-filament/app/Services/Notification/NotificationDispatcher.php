<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Enums\NotificationDeliveryStatus;
use App\Enums\NotificationPlatform;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class NotificationDispatcher
{
    /**
     * @param  array<int, NotificationPlatform>  $platforms
     * @param  array<string, mixed>|null  $dataPayload
     * @return Collection<int, Notification>
     */
    public function dispatchToPlatforms(
        User $user,
        string $title,
        string $body,
        array $platforms,
        ?array $dataPayload = null,
    ): Collection {
        $notificationKey = (string) Str::uuid();
        $notifications = collect();

        foreach ($platforms as $platform) {
            $status = $platform === NotificationPlatform::WebAdminInApp
                ? NotificationDeliveryStatus::Sent
                : NotificationDeliveryStatus::Pending;

            $notification = Notification::query()->firstOrCreate(
                [
                    'user_id' => $user->id,
                    'notification_key' => $notificationKey,
                    'platform' => $platform->value,
                ],
                [
                    'title' => $title,
                    'body' => $body,
                    'status' => $status->value,
                    'data_payload' => $dataPayload,
                    'sent_at' => $status === NotificationDeliveryStatus::Sent ? now() : null,
                    'attempt_count' => 0,
                ],
            );

            $notifications->push($notification);
        }

        return $notifications;
    }

    public function markAsSent(Notification $notification): Notification
    {
        $notification->update([
            'status' => NotificationDeliveryStatus::Sent,
            'sent_at' => now(),
            'failed_at' => null,
            'error_message' => null,
        ]);

        return $notification->refresh();
    }

    public function markAsFailed(Notification $notification, string $errorMessage): Notification
    {
        $notification->update([
            'status' => NotificationDeliveryStatus::Failed,
            'failed_at' => now(),
            'error_message' => Str::limit($errorMessage, 500),
            'attempt_count' => $notification->attempt_count + 1,
        ]);

        return $notification->refresh();
    }
}
