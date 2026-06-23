<?php

declare(strict_types=1);

namespace App\Filament\Resources\Villages\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class VillageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('district_id')
                    ->label('Kecamatan')
                    ->relationship('district', 'nama')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('nama')
                    ->label('Nama Kelurahan')
                    ->required()
                    ->maxLength(150),
            ]);
    }
}
