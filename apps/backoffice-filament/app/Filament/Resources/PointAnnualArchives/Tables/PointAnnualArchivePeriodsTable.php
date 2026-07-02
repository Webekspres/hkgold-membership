<?php

declare(strict_types=1);

namespace App\Filament\Resources\PointAnnualArchives\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PointAnnualArchivePeriodsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('archive_year')
                    ->label('Tahun Arsip')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Nama Periode')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('total_members')
                    ->label('Total Member')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('frozen_points_total')
                    ->label('Total Poin Dibekukan')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('redeemed_points_total')
                    ->label('Total Poin Ditukarkan')
                    ->numeric()
                    ->sortable(),
            ])
            ->defaultSort('archive_year', 'desc')
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
