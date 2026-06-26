<?php

declare(strict_types=1);

namespace App\Filament\Resources\Branches\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RedeemInvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'redeemInvoices';

    protected static ?string $title = 'Riwayat Redeem';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('No. Invoice')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('member.user.full_name')
                    ->label('Member')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('reward.name')
                    ->label('Reward')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('points_redeemed')
                    ->label('Poin')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->with(['member.user', 'reward']))
            ->headerActions([])
            ->recordActions([])
            ->paginated([10, 25, 50]);
    }
}
