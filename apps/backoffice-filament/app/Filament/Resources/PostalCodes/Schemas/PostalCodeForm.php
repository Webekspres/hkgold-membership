<?php

declare(strict_types=1);

namespace App\Filament\Resources\PostalCodes\Schemas;

use App\Models\PostalCode;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class PostalCodeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('city_id')
                    ->label('Kota/Kabupaten')
                    ->relationship('regency', 'nama')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('sub_district_id')
                    ->label('Kecamatan')
                    ->relationship('district', 'nama')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('kodepos')
                    ->label('Kode Pos')
                    ->required()
                    ->maxLength(20)
                    ->rules(fn (?PostalCode $record): array => [
                        Rule::unique('postal_codes', 'kodepos')->ignore($record?->id),
                    ]),
            ]);
    }
}
