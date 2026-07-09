<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Enums\DevicePushTokenPlatform;
use App\Enums\NotificationPlatform;
use App\Models\DevicePushToken;
use App\Models\Member;
use App\Models\PointInjectionDetail;
use App\Models\User;
use Illuminate\Support\Collection;

class DevicePushTokenRegistry
{
    /**
     * @return array<int, string>
     */
    public function activeTokensForUser(User $user, NotificationPlatform $platform): array
    {
        $tokenPlatform = $this->mapNotificationPlatformToTokenPlatform($platform);

        return DevicePushToken::query()
            ->active()
            ->where('user_id', $user->id)
            ->where('platform', $tokenPlatform->value)
            ->pluck('token')
            ->all();
    }

    /**
     * @param  array<string, mixed>  $criteria
     * @return array<int, string>
     */
    public function tokensForCriteria(array $criteria, NotificationPlatform $platform): array
    {
        $userIds = $this->resolveUserIdsForCriteria($criteria);

        if ($userIds->isEmpty()) {
            return [];
        }

        $tokenPlatform = $this->mapNotificationPlatformToTokenPlatform($platform);

        return DevicePushToken::query()
            ->active()
            ->where('platform', $tokenPlatform->value)
            ->whereIn('user_id', $userIds)
            ->pluck('token')
            ->unique()
            ->values()
            ->all();
    }

    public function markTokenUsedByValue(string $token): void
    {
        DevicePushToken::query()
            ->active()
            ->where('token', $token)
            ->update([
                'last_used_at' => now(),
            ]);
    }

    /**
     * @param  array<string, mixed>  $criteria
     * @return Collection<int, string>
     */
    private function resolveUserIdsForCriteria(array $criteria): Collection
    {
        $type = (string) ($criteria['type'] ?? '');

        return match ($type) {
            'tier' => Member::query()
                ->where('is_suspended', false)
                ->whereNotNull('user_id')
                ->where('current_tier', (string) ($criteria['tier'] ?? ''))
                ->pluck('user_id'),
            'batch' => $this->resolveUserIdsForBatch((string) ($criteria['batch_id'] ?? '')),
            default => Member::query()
                ->where('is_suspended', false)
                ->whereNotNull('user_id')
                ->pluck('user_id'),
        };
    }

    /**
     * @return Collection<int, string>
     */
    private function resolveUserIdsForBatch(string $batchId): Collection
    {
        if ($batchId === '') {
            return collect();
        }

        $memberNumbers = PointInjectionDetail::query()
            ->where('batch_id', $batchId)
            ->pluck('raw_member_number')
            ->filter()
            ->unique()
            ->values();

        if ($memberNumbers->isEmpty()) {
            return collect();
        }

        return Member::query()
            ->whereIn('member_number', $memberNumbers)
            ->whereNotNull('user_id')
            ->pluck('user_id');
    }

    private function mapNotificationPlatformToTokenPlatform(NotificationPlatform $platform): DevicePushTokenPlatform
    {
        return match ($platform) {
            NotificationPlatform::WebBrowserPush => DevicePushTokenPlatform::Web,
            NotificationPlatform::MobileAppPush => DevicePushTokenPlatform::Mobile,
            NotificationPlatform::WebAdminInApp => DevicePushTokenPlatform::Mobile,
        };
    }
}
