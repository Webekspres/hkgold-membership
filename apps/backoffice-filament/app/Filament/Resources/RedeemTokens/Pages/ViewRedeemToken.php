<?php

declare(strict_types=1);

namespace App\Filament\Resources\RedeemTokens\Pages;

use App\Filament\Resources\RedeemTokens\RedeemTokenResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewRedeemToken extends ViewRecord
{
    protected static string $resource = RedeemTokenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Kembali ke List')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(RedeemTokenResource::getUrl('index')),
        ];
    }
}
