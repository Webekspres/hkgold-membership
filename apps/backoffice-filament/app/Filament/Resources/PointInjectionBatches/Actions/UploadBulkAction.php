<?php

declare(strict_types=1);

namespace App\Filament\Resources\PointInjectionBatches\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;

class UploadBulkAction
{
    public static function make(): Action
    {
        return Action::make('uploadBulk')
            ->label('Upload Bulk')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('primary')
            ->goldStyle()
            ->action(function (): void {
                Notification::make()
                    ->title('Upload Bulk')
                    ->body('Fitur upload bulk akan segera hadir.')
                    ->info()
                    ->send();
            });
    }
}
