<?php

declare(strict_types=1);

namespace App\Filament\Resources\Cities\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')
                    ->label('Nama Kota/Kabupaten')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('province.nama')
                    ->label('Provinsi')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('sub_districts_count')
                    ->label('Jumlah Kecamatan')
                    ->counts('subDistricts')
                    ->sortable(),
            ])
            ->defaultSort('nama')
            ->filters([
                SelectFilter::make('province_id')
                    ->label('Provinsi')
                    ->relationship('province', 'nama')
                    ->placeholder('Semua provinsi')
                    ->native(false)
                    ->searchable()
                    ->preload(),
            ], layout: FiltersLayout::Hidden)
            ->deferFilters(false)
            ->hiddenFilterIndicators()
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ]);
    }
}
