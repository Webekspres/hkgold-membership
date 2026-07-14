<?php

declare(strict_types=1);

namespace App\Filament\Resources\NotificationCampaigns\Pages;

use App\Filament\Resources\NotificationCampaigns\Actions\BroadcastNotificationAction;
use App\Filament\Resources\NotificationCampaigns\NotificationCampaignResource;
use App\Filament\Resources\NotificationCampaigns\Widgets\NotificationCampaignStatsWidget;
use Filament\Resources\Pages\ListRecords;

class ListNotificationCampaigns extends ListRecords
{
    protected static string $resource = NotificationCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            BroadcastNotificationAction::make(),
        ];
    }

    /**
     * @return array<class-string>
     */
    public function getHeaderWidgets(): array
    {
        return [
            NotificationCampaignStatsWidget::class,
        ];
    }

    /**
     * @return int|array<string, int|null>
     */
    public function getHeaderWidgetsColumns(): int|array
    {
        return [
            'default' => 1,
            'md' => 3,
            'xl' => 3,
        ];
    }
}
