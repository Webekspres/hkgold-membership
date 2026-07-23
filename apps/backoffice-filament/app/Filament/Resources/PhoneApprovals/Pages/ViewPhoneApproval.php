<?php

declare(strict_types=1);

namespace App\Filament\Resources\PhoneApprovals\Pages;

use App\Filament\Resources\PhoneApprovals\Actions\ApprovePhoneChangeAction;
use App\Filament\Resources\PhoneApprovals\Actions\RejectPhoneChangeAction;
use App\Filament\Resources\PhoneApprovals\PhoneApprovalResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewPhoneApproval extends ViewRecord
{
    protected static string $resource = PhoneApprovalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ApprovePhoneChangeAction::make(),
            RejectPhoneChangeAction::make(),
            Action::make('back')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(PhoneApprovalResource::getUrl('index')),
        ];
    }
}
