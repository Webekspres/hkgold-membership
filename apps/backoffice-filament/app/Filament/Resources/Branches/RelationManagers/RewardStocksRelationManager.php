<?php

declare(strict_types=1);

namespace App\Filament\Resources\Branches\RelationManagers;

use App\Models\BranchRewardStock;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RewardStocksRelationManager extends RelationManager
{
    protected static string $relationship = 'rewardStocks';

    protected static ?string $title = 'Stock Reward';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reward.name')
                    ->label('Reward')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('reward.categoryReward.name')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('actual_stock')
                    ->label('Stok aktual')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('held_stock')
                    ->label('Stok ditahan')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('available_stock')
                    ->label('Tersedia')
                    ->state(fn (BranchRewardStock $record): int => $record->actual_stock - $record->held_stock)
                    ->numeric()
                    ->sortable(query: function ($query, string $direction) {
                        return $query->orderByRaw('(actual_stock - held_stock) '.$direction);
                    }),
            ])
            ->defaultSort('reward.name')
            ->modifyQueryUsing(fn ($query) => $query->with(['reward.categoryReward']))
            ->headerActions([])
            ->recordActions([])
            ->paginated([10, 25, 50]);
    }
}
