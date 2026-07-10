<?php

declare(strict_types=1);

namespace App\Filament\Resources\NotificationCampaigns\Support;

use App\Enums\CampaignStatus;
use App\Models\NotificationCampaign;
use Illuminate\Database\Eloquent\Builder;

class NotificationCampaignSupport
{
    public static function campaignsThisMonth(): int
    {
        return self::monthlyQuery()->count();
    }

    public static function totalTargetedThisMonth(): int
    {
        return (int) self::monthlyQuery()->sum('targeted_count');
    }

    public static function completedCampaignsThisMonth(): int
    {
        return self::monthlyQuery()
            ->where('status', CampaignStatus::Completed)
            ->count();
    }

    public static function formatNumber(int $value): string
    {
        return number_format($value, 0, ',', '.');
    }

    /**
     * @return Builder<NotificationCampaign>
     */
    private static function monthlyQuery(): Builder
    {
        return NotificationCampaign::query()
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month);
    }
}
