<?php

declare(strict_types=1);

namespace App\Filament\Resources\PointMutations\Tables;

use App\Filament\Resources\PointMutations\Support\PointMutationSupport;
use App\Filament\Support\IndonesianDateTimeFormatter;
use App\Models\Branch;
use App\Models\PointMutation;
use App\Models\TransactionType;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PointMutationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('transaction_date')
                    ->label('Tanggal')
                    ->formatStateUsing(fn (mixed $state): ?string => IndonesianDateTimeFormatter::tableDate($state))
                    ->tooltip(fn (mixed $state): ?string => IndonesianDateTimeFormatter::tableDateTooltip($state))
                    ->sortable(),

                TextColumn::make('member.user.full_name')
                    ->label('Nama Member')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('member', function (Builder $query) use ($search): void {
                            $query->where('member_number', 'like', "%{$search}%")
                                ->orWhereHas('user', fn (Builder $query): Builder => $query->where('full_name', 'like', "%{$search}%"));
                        });
                    })
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('transactionType.display_name')
                    ->label('Tipe Transaksi')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('purchase_nominal')
                    ->label('Pembelian')
                    ->prefix('Rp ')
                    ->numeric(decimalPlaces: 0, thousandsSeparator: ',')
                    ->sortable(),

                TextColumn::make('points_delta')
                    ->label('Poin')
                    ->state(fn (PointMutation $record): string => PointMutationSupport::formatPointsDelta($record)['formatted'])
                    ->badge()
                    ->color(fn (PointMutation $record): string => PointMutationSupport::formatPointsDelta($record)['color']),

                TextColumn::make('balance_snapshot')
                    ->label('Sisa Balance')
                    ->numeric(thousandsSeparator: ',')
                    ->sortable(),

                IconColumn::make('reference_id')
                    ->label('Reference')
                    ->icon(fn (?string $state): ?string => filled($state) ? 'heroicon-o-link' : null)
                    ->color('gray')
                    ->tooltip('Segera hadir'),
            ])
            ->defaultSort('transaction_date', 'desc')
            ->filters([
                Filter::make('point_filters')
                    ->label('')
                    ->schema([
                        DatePicker::make('from')
                            ->label('Dari'),
                        DatePicker::make('until')
                            ->label('Sampai'),
                        Select::make('branch_id')
                            ->label('Cabang')
                            ->options(fn (): array => Branch::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()
                            ->preload(),
                        Select::make('transaction_type_id')
                            ->label('Tipe Transaksi')
                            ->options(fn (): array => TransactionType::query()->orderBy('display_name')->pluck('display_name', 'id')->all())
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(4)
                    ->columnSpanFull()
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $query, string $date): Builder => $query->whereDate('transaction_date', '>=', $date),
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn (Builder $query, string $date): Builder => $query->whereDate('transaction_date', '<=', $date),
                            )
                            ->when(
                                $data['branch_id'] ?? null,
                                fn (Builder $query, string $branchId): Builder => $query->where('branch_id', $branchId),
                            )
                            ->when(
                                $data['transaction_type_id'] ?? null,
                                fn (Builder $query, string $transactionTypeId): Builder => $query->where('transaction_type_id', $transactionTypeId),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if (filled($data['from'] ?? null)) {
                            $indicators[] = Indicator::make('Dari: '.$data['from'])
                                ->removeField('from');
                        }

                        if (filled($data['until'] ?? null)) {
                            $indicators[] = Indicator::make('Sampai: '.$data['until'])
                                ->removeField('until');
                        }

                        if (filled($data['branch_id'] ?? null)) {
                            $branchName = Branch::query()->whereKey($data['branch_id'])->value('name');
                            $indicators[] = Indicator::make('Cabang: '.($branchName ?? $data['branch_id']))
                                ->removeField('branch_id');
                        }

                        if (filled($data['transaction_type_id'] ?? null)) {
                            $typeName = TransactionType::query()->whereKey($data['transaction_type_id'])->value('display_name');
                            $indicators[] = Indicator::make('Tipe: '.($typeName ?? $data['transaction_type_id']))
                                ->removeField('transaction_type_id');
                        }

                        return $indicators;
                    }),
            ])
            ->filtersFormColumns(1)
            ->filtersFormWidth(Width::Full)
            ->filtersLayout(FiltersLayout::AboveContentCollapsible)
            ->filtersTriggerAction(
                fn (Action $action): Action => $action
                    ->iconButton()
                    ->icon('heroicon-o-funnel'),
            )
            ->recordActions([])
            ->headerActions([])
            ->paginated([10, 25, 50]);
    }
}
