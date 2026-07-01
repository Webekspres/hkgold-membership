<?php

declare(strict_types=1);

namespace App\Filament\Resources\Staff\Pages;

use App\Filament\Resources\Staff\StaffResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStaff extends ListRecords
{
    protected static string $resource = StaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Staff')
                ->color('primary')
                ->extraAttributes([
                    'style' => 'background: linear-gradient(135deg, #f5c842, #e8a020); border: none;',
                    'class' => 'text-black font-bold hover:opacity-90 shadow-lg',
                ]),
        ];
    }
}
