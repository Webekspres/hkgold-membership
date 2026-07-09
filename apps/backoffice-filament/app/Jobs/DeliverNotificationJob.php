<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\NotificationPlatform;
use App\Models\Notification;
use App\Services\Notification\DevicePushTokenRegistry;
use App\Services\Notification\FcmPushDriver;
use App\Services\Notification\NotificationDispatcher;
use App\Services\Notification\PushDriverInterface;
use App\Services\Notification\WebPushDriver;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class DeliverNotificationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 30, 60];

    public function __construct(public readonly string $notificationId)
    {
        $this->onQueue(config('notifications.queue', 'notifications'));
    }

    public function handle(
        NotificationDispatcher $dispatcher,
        DevicePushTokenRegistry $tokenRegistry,
        FcmPushDriver $fcmPushDriver,
        WebPushDriver $webPushDriver,
    ): void {
        $notification = Notification::query()
            ->with('user')
            ->find($this->notificationId);

        if ($notification === null) {
            Log::warning('DeliverNotificationJob: notifikasi tidak ditemukan.', [
                'notification_id' => $this->notificationId,
            ]);

            return;
        }

        if ($notification->platform === NotificationPlatform::WebAdminInApp) {
            return;
        }

        if (! in_array($notification->platform, [NotificationPlatform::MobileAppPush, NotificationPlatform::WebBrowserPush], true)) {
            return;
        }

        $user = $notification->user;

        if ($user === null) {
            $dispatcher->markAsFailed($notification, 'User notifikasi tidak ditemukan.');

            return;
        }

        $driver = $this->resolveDriver($notification->platform, $fcmPushDriver, $webPushDriver);
        $tokens = $tokenRegistry->activeTokensForUser($user, $notification->platform);

        if ($tokens === []) {
            $dispatcher->markAsFailed($notification, 'Tidak ada token push terdaftar.');

            return;
        }

        if (! $this->driverIsConfigured($notification->platform, $fcmPushDriver, $webPushDriver)) {
            $dispatcher->markAsFailed($notification, $this->configurationErrorMessage($notification->platform));

            return;
        }

        /** @var array<string, string> $payload */
        $payload = collect($notification->data_payload ?? [])
            ->filter(fn (mixed $value): bool => is_scalar($value) || $value === null)
            ->mapWithKeys(fn (mixed $value, string|int $key): array => [(string) $key => (string) $value])
            ->all();

        $successCount = 0;
        $lastError = null;

        foreach ($tokens as $token) {
            $result = $driver->sendToToken(
                token: $token,
                title: $notification->title,
                body: $notification->body,
                data: $payload,
            );

            if ($result->success) {
                $successCount++;
                $tokenRegistry->markTokenUsedByValue($token);
            } else {
                $lastError = $result->errorMessage;
            }
        }

        if ($successCount > 0) {
            $dispatcher->markAsSent($notification);

            return;
        }

        $dispatcher->markAsFailed(
            $notification,
            $lastError ?? 'Gagal mengirim notifikasi push ke semua perangkat.',
        );
    }

    private function resolveDriver(
        NotificationPlatform $platform,
        FcmPushDriver $fcmPushDriver,
        WebPushDriver $webPushDriver,
    ): PushDriverInterface {
        return match ($platform) {
            NotificationPlatform::WebBrowserPush => $webPushDriver,
            NotificationPlatform::MobileAppPush => $fcmPushDriver,
            NotificationPlatform::WebAdminInApp => $fcmPushDriver,
        };
    }

    private function driverIsConfigured(
        NotificationPlatform $platform,
        FcmPushDriver $fcmPushDriver,
        WebPushDriver $webPushDriver,
    ): bool {
        return match ($platform) {
            NotificationPlatform::WebBrowserPush => $webPushDriver->isConfigured(),
            NotificationPlatform::MobileAppPush => $fcmPushDriver->isConfigured(),
            NotificationPlatform::WebAdminInApp => false,
        };
    }

    private function configurationErrorMessage(NotificationPlatform $platform): string
    {
        return match ($platform) {
            NotificationPlatform::WebBrowserPush => 'Web Push VAPID tidak dikonfigurasi.',
            NotificationPlatform::MobileAppPush => 'FCM credential tidak dikonfigurasi.',
            NotificationPlatform::WebAdminInApp => 'Platform in-app tidak memerlukan push.',
        };
    }
}
