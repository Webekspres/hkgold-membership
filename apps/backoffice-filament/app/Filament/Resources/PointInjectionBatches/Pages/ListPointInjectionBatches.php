<?php

declare(strict_types=1);

namespace App\Filament\Resources\PointInjectionBatches\Pages;

use App\Filament\Resources\PointInjectionBatches\Actions\UploadBulkAction;
use App\Filament\Resources\PointInjectionBatches\PointInjectionBatchResource;
use Filament\Resources\Pages\ListRecords;

class ListPointInjectionBatches extends ListRecords
{
    protected static string $resource = PointInjectionBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            UploadBulkAction::make(),
        ];
    }
}
