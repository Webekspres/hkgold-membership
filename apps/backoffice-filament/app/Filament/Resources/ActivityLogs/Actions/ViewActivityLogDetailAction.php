<?php

declare(strict_types=1);

namespace App\Filament\Resources\ActivityLogs\Actions;

use App\Filament\Resources\ActivityLogs\Schemas\ActivityLogInfolist;
use App\Models\ActivityLog;
use Filament\Actions\Action;
use Filament\Support\Enums\Width;

class ViewActivityLogDetailAction
{
    public static function make(): Action
    {
        return Action::make('viewActivityLogDetail')
            ->label('Detail')
            ->icon('heroicon-o-eye')
            ->color('gray')
            ->modalHeading(fn (ActivityLog $record): string => 'Detail Log — '.($record->description))
            ->modalWidth(Width::TwoExtraLarge)
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Tutup')
            ->infolist(fn ($infolist) => ActivityLogInfolist::configure($infolist));
    }
}
