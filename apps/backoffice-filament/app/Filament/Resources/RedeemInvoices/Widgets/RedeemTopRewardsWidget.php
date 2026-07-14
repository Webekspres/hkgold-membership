<?php

declare(strict_types=1);

namespace App\Filament\Resources\RedeemInvoices\Widgets;

use App\Models\Reward;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class RedeemTopRewardsWidget extends BaseWidget
{
    protected static ?string $heading = 'Reward Paling Banyak Ditukar';

    protected int|string|array $columnSpan = [
        'default' => 1,
        'lg' => 2,
    ];

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Reward::query()
                    ->select([
                        'rewards.id',
                        'rewards.name as reward_name',
                    ])
                    ->withCount([
                        'invoices as total_redeemed' => fn (Builder $query): Builder => $query
                            ->where('redeem_invoices.created_at', '>=', now()->subDays(30)),
                    ])
                    ->having('total_redeemed', '>', 0)
                    ->orderByDesc('total_redeemed')
                    ->limit(5),
            )
            ->columns([
                TextColumn::make('no')
                    ->label('No')
                    ->rowIndex(),
                TextColumn::make('reward_name')
                    ->label('Nama Reward'),
                TextColumn::make('total_redeemed')
                    ->label('')
                    ->numeric(thousandsSeparator: '.'),
            ])
            ->paginated(false);
    }
}
