<?php

declare(strict_types=1);

namespace App\Filament\Resources\NotificationCampaigns\Pages;

use App\Filament\Resources\NotificationCampaigns\NotificationCampaignResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewNotificationCampaign extends ViewRecord
{
    protected static string $resource = NotificationCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Kembali ke List')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(NotificationCampaignResource::getUrl('index')),
        ];
    }
}
