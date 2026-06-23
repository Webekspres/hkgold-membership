<?php

declare(strict_types=1);

namespace App\Filament\Resources\Districts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DistrictForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('regency_id')
                    ->label('Kota/Kabupaten')
                    ->relationship('regency', 'nama')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('nama')
                    ->label('Nama Kecamatan')
                    ->required()
                    ->maxLength(150),
            ]);
    }
}
