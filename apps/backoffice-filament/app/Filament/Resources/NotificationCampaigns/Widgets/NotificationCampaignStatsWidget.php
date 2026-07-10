<?php

declare(strict_types=1);

namespace App\Filament\Resources\NotificationCampaigns\Widgets;

use App\Filament\Resources\NotificationCampaigns\Support\NotificationCampaignSupport;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class NotificationCampaignStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected int|array|null $columns = 3;

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        return [
            Stat::make('Kampanye Bulan Ini', NotificationCampaignSupport::formatNumber(NotificationCampaignSupport::campaignsThisMonth()))
                ->description('Total kampanye dibuat')
                ->icon('heroicon-o-megaphone'),
            Stat::make('Total Ditarget Bulan Ini', NotificationCampaignSupport::formatNumber(NotificationCampaignSupport::totalTargetedThisMonth()))
                ->description('Jumlah orang ditarget')
                ->icon('heroicon-o-users'),
            Stat::make('Kampanye Selesai Bulan Ini', NotificationCampaignSupport::formatNumber(NotificationCampaignSupport::completedCampaignsThisMonth()))
                ->description('Status selesai')
                ->icon('heroicon-o-check-circle'),
        ];
    }
}
