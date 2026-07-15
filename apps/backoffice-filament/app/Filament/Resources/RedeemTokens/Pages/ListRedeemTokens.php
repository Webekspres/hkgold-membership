<?php

declare(strict_types=1);

namespace App\Filament\Resources\RedeemTokens\Pages;

use App\Filament\Resources\RedeemTokens\Actions\VerifyRedeemTokenAction;
use App\Filament\Resources\RedeemTokens\RedeemTokenResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListRedeemTokens extends ListRecords
{
    protected static string $resource = RedeemTokenResource::class;

    protected function getHeaderActions(): array
    {
        if (! Auth::user()?->can('Update:RedeemToken')) {
            return [];
        }

        return [
            VerifyRedeemTokenAction::make(),
        ];
    }
}
