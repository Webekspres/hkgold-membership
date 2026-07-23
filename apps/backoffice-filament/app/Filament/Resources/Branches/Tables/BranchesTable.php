<?php

declare(strict_types=1);

namespace App\Filament\Resources\Branches\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BranchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('branch_code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->copyable(),
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('normalizedAddress.village.subDistrict.city.nama')
                    ->label('Kota')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_online_warehouse')
                    ->label('Online')
                    ->boolean()
                    ->sortable(),
            ])
            ->defaultSort('branch_code')
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                ]),
            ]);
    }
}
