<?php

declare(strict_types=1);

namespace App\Filament\Resources\CategoryRewards\Pages;

use App\Filament\Resources\CategoryRewards\CategoryRewardResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCategoryRewards extends ListRecords
{
    protected static string $resource = CategoryRewardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah kategori'),
        ];
    }
}
