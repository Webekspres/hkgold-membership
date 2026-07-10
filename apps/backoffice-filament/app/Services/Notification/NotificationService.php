<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Enums\CampaignStatus;
use App\Enums\NotificationPlatform;
use App\Jobs\BroadcastNotificationJob;
use App\Jobs\DeliverNotificationJob;
use App\Models\Member;
use App\Models\Notification;
use App\Models\NotificationCampaign;
use App\Models\PointInjectionDetail;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Throwable;

class NotificationService
{
    public function __construct(
        private readonly NotificationDispatcher $dispatcher,
    ) {}

    /**
     * @param  array<int, NotificationPlatform>  $platforms
     * @param  array<string, mixed>|null  $payload
     */
    public function notifyUser(
        User $user,
        string $title,
        string $body,
        array $platforms,
        ?array $payload = null,
    ): void {
        $notifications = $this->dispatcher->dispatchToPlatforms(
            user: $user,
            title: $title,
            body: $body,
            platforms: $platforms,
            dataPayload: $payload,
        );

        foreach ($notifications as $notification) {
            if ($notification->platform === NotificationPlatform::WebAdminInApp) {
                continue;
            }

            $this->enqueueDeliverJob($notification);
        }
    }

    /**
     * @param  array<string, mixed>  $criteria
     * @param  array<int, NotificationPlatform|string>  $platforms
     */
    public function broadcastMass(
        string $title,
        string $body,
        array $criteria,
        array $platforms,
        ?User $createdBy = null,
    ): void {
        $normalizedPlatforms = array_map(
            fn (NotificationPlatform|string $platform): string => $platform instanceof NotificationPlatform
                ? $platform->value
                : $platform,
            $platforms,
        );

        $campaign = NotificationCampaign::query()->create([
            'title' => $title,
            'body' => $body,
            'platforms' => $normalizedPlatforms,
            'criteria_json' => $criteria,
            'targeted_count' => $this->resolveTargetedCount($criteria),
            'status' => CampaignStatus::Pending,
            'created_by_id' => $createdBy?->id,
        ]);

        $this->enqueueBroadcastJob($campaign);
    }

    /**
     * @param  array<string, mixed>  $criteria
     */
    public function resolveTargetedCount(array $criteria): int
    {
        $type = (string) ($criteria['type'] ?? '');

        return match ($type) {
            'all_active_members' => Member::query()
                ->where('is_suspended', false)
                ->whereNotNull('user_id')
                ->count(),
            'tier' => Member::query()
                ->where('is_suspended', false)
                ->whereNotNull('user_id')
                ->where('current_tier', (string) ($criteria['tier'] ?? ''))
                ->count(),
            'batch' => PointInjectionDetail::query()
                ->where('batch_id', (string) ($criteria['batch_id'] ?? ''))
                ->count(),
            default => 0,
        };
    }

    private function enqueueDeliverJob(Notification $notification): void
    {
        try {
            DeliverNotificationJob::dispatch($notification->id)->afterCommit();
        } catch (Throwable $exception) {
            Log::warning('Gagal mengantre notifikasi personalized.', [
                'notification_id' => $notification->id,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function enqueueBroadcastJob(NotificationCampaign $campaign): void
    {
        try {
            BroadcastNotificationJob::dispatch($campaign->id)->afterCommit();
        } catch (Throwable $exception) {
            Log::warning('Gagal mengantre broadcast notifikasi.', [
                'campaign_id' => $campaign->id,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
