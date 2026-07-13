<?php

declare(strict_types=1);

namespace App\Filament\Resources\RedeemInvoices\Tables;

use App\Enums\RedeemStatus;
use App\Filament\Support\IndonesianDateTimeFormatter;
use App\Models\Branch;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RedeemInvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('No. Invoice')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('created_at')
                    ->label('Waktu Penukaran')
                    ->formatStateUsing(fn (mixed $state): ?string => IndonesianDateTimeFormatter::tableDate($state))
                    ->tooltip(fn (mixed $state): ?string => IndonesianDateTimeFormatter::tableDateTooltip($state))
                    ->sortable(),
                TextColumn::make('member.member_number')
                    ->label('Kode Member')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('member.user.full_name')
                    ->label('Nama Member')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('reward.name')
                    ->label('Nama Reward')
                    ->searchable(),
                TextColumn::make('branch.name')
                    ->label('Nama Branch')
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('staff.user.full_name')
                    ->label('Nama Staff')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('points_redeemed')
                    ->label('Poin Ditukarkan')
                    ->numeric(thousandsSeparator: '.')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('period')
                    ->label('Rentang Tanggal')
                    ->schema([
                        DatePicker::make('from')->label('Dari'),
                        DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
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
                                RedeemStatus::Completed->value => RedeemStatus::Completed->label(),
                                RedeemStatus::Refunded->value => RedeemStatus::Refunded->label(),
                            ]),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['status'] ?? null,
                        fn (Builder $query, string $status): Builder => $query->where('status', $status),
                    )),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50]);
    }
}
