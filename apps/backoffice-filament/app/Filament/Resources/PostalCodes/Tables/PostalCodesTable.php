<?php

declare(strict_types=1);

namespace App\Filament\Resources\PostalCodes\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PostalCodesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kodepos')
                    ->label('Kode Pos')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('city.nama')
                    ->label('Kota/Kabupaten')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('subDistrict.nama')
                    ->label('Kecamatan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('addresses_count')
                    ->label('Jumlah Alamat')
                    ->counts('addresses')
                    ->sortable(),
            ])
            ->defaultSort('kodepos')
            ->filters([])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ]);
    }
}
