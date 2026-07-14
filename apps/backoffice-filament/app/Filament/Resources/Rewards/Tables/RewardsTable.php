<?php

declare(strict_types=1);

namespace App\Filament\Resources\Rewards\Tables;

use App\Filament\Support\IndonesianDateTimeFormatter;
use App\Models\Reward;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class RewardsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('categoryReward.name')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('points_required')
                    ->label('Poin')
                    ->numeric(thousandsSeparator: ',')
                    ->sortable(),

                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('start_at')
                    ->label('Mulai')
                    ->formatStateUsing(fn (mixed $state): ?string => IndonesianDateTimeFormatter::tableDate($state))
                    ->tooltip(fn (mixed $state): ?string => IndonesianDateTimeFormatter::tableDateTooltip($state))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('end_at')
                    ->label('Berakhir')
                    ->formatStateUsing(fn (mixed $state): ?string => IndonesianDateTimeFormatter::tableDate($state))
                    ->tooltip(fn (mixed $state): ?string => IndonesianDateTimeFormatter::tableDateTooltip($state))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('branch_stocks_sum_actual_stock')
                    ->label('Actual Stock')
                    ->numeric()
                    ->default(0)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('branch_stocks_sum_held_stock')
                    ->label('Held Stock')
                    ->numeric()
                    ->default(0)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('total_stock')
                    ->label('Total Stock')
                    ->state(fn (Reward $record): int => (int) ($record->branch_stocks_sum_actual_stock ?? 0) + (int) ($record->branch_stocks_sum_held_stock ?? 0))
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status aktif')
                    ->placeholder('Semua')
                    ->trueLabel('Aktif')
                    ->falseLabel('Nonaktif'),
            ], layout: FiltersLayout::Hidden)
            ->deferFilters(false)
            ->hiddenFilterIndicators()
            ->recordActions([
                ViewAction::make()
                    ->label('Lihat'),
            ]);
    }
}
