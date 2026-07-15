<?php

declare(strict_types=1);

namespace App\Filament\Resources\RedeemTokens\Tables;

use App\Filament\Support\IndonesianDateTimeFormatter;
use App\Models\Branch;
use App\Models\RedeemToken;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RedeemTokensTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('token_code')
                    ->label('Kode Token')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),
                TextColumn::make('member.member_number')
                    ->label('Kode Member')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('member.user.full_name')
                    ->label('Nama Member')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('reward.name')
                    ->label('Reward')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('held_points')
                    ->label('Poin Ditahan')
                    ->numeric(thousandsSeparator: '.')
                    ->sortable(),
                TextColumn::make('expired_at')
                    ->label('Kedaluwarsa')
                    ->formatStateUsing(fn (mixed $state): ?string => IndonesianDateTimeFormatter::tableDate($state))
                    ->tooltip(fn (mixed $state): ?string => IndonesianDateTimeFormatter::tableDateTooltip($state))
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->state(fn (RedeemToken $record): string => self::statusLabel($record))
                    ->color(fn (RedeemToken $record): string => self::statusColor($record)),
            ])
            ->filters([
                Filter::make('branch')
                    ->label('Cabang')
                    ->schema([
                        Select::make('branch_id')
                            ->label('Cabang')
                            ->options(fn (): array => Branch::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()
                            ->preload(),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['branch_id'] ?? null,
                        fn (Builder $query, int $branchId): Builder => $query->where('branch_id', $branchId),
                    )),
                Filter::make('status')
                    ->label('Status')
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'available' => 'Menunggu',
                                'used' => 'Terpakai',
                                'expired' => 'Kedaluwarsa',
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['status'] ?? null) {
                            'available' => $query->where('is_used', false)->where('expired_at', '>', now()),
                            'used' => $query->where('is_used', true),
                            'expired' => $query->where('is_used', false)->where('expired_at', '<=', now()),
                            default => $query,
                        };
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50]);
    }

    public static function statusLabel(RedeemToken $record): string
    {
        if ($record->is_used) {
            return 'Terpakai';
        }

        if ($record->expired_at->isPast()) {
            return 'Kedaluwarsa';
        }

        return 'Menunggu';
    }

    public static function statusColor(RedeemToken $record): string
    {
        if ($record->is_used) {
            return 'gray';
        }

        if ($record->expired_at->isPast()) {
            return 'danger';
        }

        return 'success';
    }
}
