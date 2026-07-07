<?php

declare(strict_types=1);

namespace App\Filament\Resources\PointAnnualArchives\Tables;

use App\Filament\Support\IndonesianDateTimeFormatter;
use App\Models\PointAnnualArchivePeriod;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
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
                Filter::make('archive_filters')
                    ->label('')
                    ->schema([
                        Select::make('archive_year')
                            ->label('Tahun Arsip')
                            ->options(fn (): array => PointAnnualArchivePeriod::query()
                                ->orderByDesc('archive_year')
                                ->pluck('archive_year', 'archive_year')
                                ->all())
                            ->searchable()
                            ->preload(),

                        Select::make('archived')
                            ->label('Status Arsip')
                            ->options([
                                '1' => 'Sudah Diarsipkan',
                                '0' => 'Belum Diarsipkan',
                            ])
                            ->placeholder('Semua')
                            ->native(false),

                        DatePicker::make('from')
                            ->label('Dari'),

                        DatePicker::make('until')
                            ->label('Sampai'),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                filled($data['archive_year'] ?? null),
                                fn (Builder $query): Builder => $query->where('archive_year', $data['archive_year']),
                            )
                            ->when(
                                filled($data['archived'] ?? null),
                                fn (Builder $query): Builder => (bool) $data['archived']
                                    ? $query->whereNotNull('archived_at')
                                    : $query->whereNull('archived_at'),
                            )
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

                        if (filled($data['archive_year'] ?? null)) {
                            $indicators[] = Indicator::make('Tahun: '.$data['archive_year'])
                                ->removeField('archive_year');
                        }

                        if (filled($data['archived'] ?? null)) {
                            $archivedLabel = (bool) $data['archived'] ? 'Sudah Diarsipkan' : 'Belum Diarsipkan';
                            $indicators[] = Indicator::make('Status: '.$archivedLabel)
                                ->removeField('archived');
                        }

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
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
