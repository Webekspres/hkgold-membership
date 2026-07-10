<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\CampaignStatus;
use App\Enums\NotificationPlatform;
use App\Models\NotificationCampaign;
use App\Services\Notification\DevicePushTokenRegistry;
use App\Services\Notification\FcmPushDriver;
use App\Services\Notification\WebPushDriver;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class BroadcastNotificationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 30, 60];

    public function __construct(public readonly string $campaignId)
    {
        $this->onQueue(config('notifications.queue', 'notifications'));
    }

    public function handle(
        DevicePushTokenRegistry $tokenRegistry,
        FcmPushDriver $fcmPushDriver,
        WebPushDriver $webPushDriver,
    ): void {
        $campaign = NotificationCampaign::query()->find($this->campaignId);

        if ($campaign === null) {
            Log::warning('BroadcastNotificationJob: campaign tidak ditemukan.', [
                'campaign_id' => $this->campaignId,
            ]);

            return;
        }

        $campaign->update([
            'status' => CampaignStatus::Processing,
        ]);

        try {
            $platform = $this->resolvePrimaryPlatform($campaign->platforms ?? []);

            if ($platform === null) {
                $this->markCampaignFailed($campaign, 'Platform campaign tidak dikenali.');

                return;
            }

            $driver = match ($platform) {
                NotificationPlatform::WebBrowserPush => $webPushDriver,
                NotificationPlatform::MobileAppPush => $fcmPushDriver,
                NotificationPlatform::WebAdminInApp => $fcmPushDriver,
            };

            if (! $this->driverIsConfigured($platform, $fcmPushDriver, $webPushDriver)) {
                $this->markCampaignFailed($campaign, $this->configurationErrorMessage($platform));

                return;
            }

            $criteria = $campaign->criteria_json ?? [];
            $tokens = $tokenRegistry->tokensForCriteria($criteria, $platform);

            if ($tokens === []) {
                $this->markCampaignFailed($campaign, 'Tidak ada token push terdaftar untuk audience ini.');

                return;
            }

            $result = $driver->sendMulticast(
                tokens: $tokens,
                title: $campaign->title,
                body: $campaign->body,
                data: [
                    'campaign_id' => $campaign->id,
                    'type' => 'broadcast',
                ],
            );

            if (! $result->success) {
                $this->markCampaignFailed(
                    $campaign,
                    $result->errorMessage ?? 'Gagal mengirim broadcast push.',
                    $result->successCount,
                    $result->failureCount,
                );

                return;
            }

            $campaign->update([
                'status' => CampaignStatus::Completed,
                'accepted_count' => $result->successCount,
                'failed_count' => $result->failureCount,
                'error_message' => null,
            ]);
        } catch (Throwable $exception) {
            Log::warning('BroadcastNotificationJob: error tidak terduga.', [
                'campaign_id' => $campaign->id,
                'message' => $exception->getMessage(),
            ]);

            $this->markCampaignFailed($campaign, Str::limit($exception->getMessage(), 500));
        }
    }

    /**
     * @param  array<int, string>  $platforms
     */
    private function resolvePrimaryPlatform(array $platforms): ?NotificationPlatform
    {
        foreach ($platforms as $platform) {
            $enum = NotificationPlatform::tryFrom($platform);

            if ($enum !== null && $enum !== NotificationPlatform::WebAdminInApp) {
                return $enum;
            }
        }

        return null;
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
            NotificationPlatform::WebBrowserPush => 'Web Push belum dikonfigurasi (VAPID public key + FCM credential).',
            NotificationPlatform::MobileAppPush => 'FCM credential tidak dikonfigurasi.',
            NotificationPlatform::WebAdminInApp => 'Platform in-app tidak mendukung broadcast push.',
        };
    }

    private function markCampaignFailed(
        NotificationCampaign $campaign,
        string $errorMessage,
        int $acceptedCount = 0,
        int $failedCount = 0,
    ): void {
        $campaign->update([
            'status' => CampaignStatus::Failed,
            'accepted_count' => $acceptedCount,
            'failed_count' => $failedCount > 0 ? $failedCount : $campaign->targeted_count,
            'error_message' => Str::limit($errorMessage, 500),
        ]);
    }
}
