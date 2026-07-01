<?php

declare(strict_types=1);

namespace App\Filament\Resources\CategoryRewards\Tables;

use App\Filament\Resources\CategoryRewards\Support\CategoryRewardFormSupport;
use App\Filament\Support\IndonesianDateTimeFormatter;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CategoryRewardsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama kategori')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('rewards_count')
                    ->label('Jumlah reward')
                    ->counts('rewards')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->formatStateUsing(fn (mixed $state): ?string => IndonesianDateTimeFormatter::tableDate($state))
                    ->tooltip(fn (mixed $state): ?string => IndonesianDateTimeFormatter::tableDateTooltip($state))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->formatStateUsing(fn (mixed $state): ?string => IndonesianDateTimeFormatter::tableDate($state))
                    ->tooltip(fn (mixed $state): ?string => IndonesianDateTimeFormatter::tableDateTooltip($state))
                    ->sortable(),
            ])
            ->defaultSort('name')
            ->filters([])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->modalWidth(Width::ExtraLarge)
                        ->mutateDataUsing(fn (array $data): array => CategoryRewardFormSupport::prepareSaveData($data)),
                    DeleteAction::make(),
                ]),
            ]);
    }
}
