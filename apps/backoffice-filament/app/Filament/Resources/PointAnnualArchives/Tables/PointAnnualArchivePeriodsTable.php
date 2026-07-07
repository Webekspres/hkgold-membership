<?php

declare(strict_types=1);

namespace App\Filament\Resources\PointAnnualArchives\Tables;

use App\Filament\Support\IndonesianDateTimeFormatter;
use App\Models\PointAnnualArchivePeriod;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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

                TextColumn::make('archived_at')
                    ->label('Tanggal Arsip')
                    ->formatStateUsing(fn (mixed $state): ?string => IndonesianDateTimeFormatter::tableDate($state))
                    ->tooltip(fn (mixed $state): ?string => IndonesianDateTimeFormatter::tableDateTooltip($state))
                    ->sortable()
                    ->placeholder('—'),

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
            ->defaultSort('archived_at', 'desc')
            ->filters([
                SelectFilter::make('archive_year')
                    ->label('Tahun Arsip')
                    ->options(fn (): array => PointAnnualArchivePeriod::query()
                        ->orderByDesc('archive_year')
                        ->pluck('archive_year', 'archive_year')
                        ->all())
                    ->searchable(),

                TernaryFilter::make('archived')
                    ->label('Status Arsip')
                    ->trueLabel('Sudah Diarsipkan')
                    ->falseLabel('Belum Diarsipkan')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereNotNull('archived_at'),
                        false: fn (Builder $query): Builder => $query->whereNull('archived_at'),
                    )
                    ->native(false),

                Filter::make('archived_at_range')
                    ->label('Rentang Tanggal Arsip')
                    ->schema([
                        DatePicker::make('from')
                            ->label('Dari'),
                        DatePicker::make('until')
                            ->label('Sampai'),
                    ])
                    ->columns(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $query, string $date): Builder => $query->whereDate('archived_at', '>=', $date),
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn (Builder $query, string $date): Builder => $query->whereDate('archived_at', '<=', $date),
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

                        return $indicators;
                    }),
            ])
            ->filtersFormColumns(2)
            ->filtersFormWidth(Width::TwoExtraLarge)
            ->filtersLayout(FiltersLayout::Modal)
            ->filtersTriggerAction(
                fn (Action $action): Action => $action
                    ->iconButton()
                    ->icon('heroicon-o-funnel'),
            )
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
