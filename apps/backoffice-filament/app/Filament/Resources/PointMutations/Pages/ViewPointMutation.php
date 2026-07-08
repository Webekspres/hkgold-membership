<?php

declare(strict_types=1);

namespace App\Filament\Resources\PointMutations\Pages;

use App\Filament\Resources\PointMutations\PointMutationResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewPointMutation extends ViewRecord
{
    protected static string $resource = PointMutationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(PointMutationResource::getUrl('index')),
        ];
    }
}
