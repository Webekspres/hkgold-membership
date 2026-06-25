<?php

declare(strict_types=1);

namespace App\Filament\Resources\SubDistricts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SubDistrictForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('city_id')
                    ->label('Kota/Kabupaten')
                    ->relationship('city', 'nama')
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
