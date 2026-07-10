<?php

declare(strict_types=1);

namespace App\Filament\Resources\ActivityLogs\Tables;

use App\Filament\Support\IndonesianDateTimeFormatter;
use App\Models\ActivityLog;
use App\Models\User;
use App\Support\ActivityLog\ActivityLogQuery;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ActivityLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->formatStateUsing(fn (mixed $state): ?string => IndonesianDateTimeFormatter::tableDate($state))
                    ->tooltip(fn (mixed $state): ?string => IndonesianDateTimeFormatter::tableDateTooltip($state))
                    ->sortable(),

                TextColumn::make('actor')
                    ->label('Pengguna')
                    ->state(fn (ActivityLog $record): string => ActivityLogQuery::actorLabel($record))
                    ->placeholder('Sistem'),

                TextColumn::make('action')
                    ->label('Aksi')
                    ->state(fn (ActivityLog $record): string => ActivityLogQuery::displayAction($record))
                    ->badge()
                    ->color('gray')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('action', $direction);
                    }),

                TextColumn::make('auditable_type')
                    ->label('Tipe Entitas')
                    ->sortable(),

                TextColumn::make('auditable_id')
                    ->label('ID Entitas')
                    ->limit(16)
                    ->tooltip(fn (ActivityLog $record): string => $record->auditable_id)
                    ->copyable()
                    ->copyableState(fn (ActivityLog $record): string => $record->auditable_id),

                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(80)
                    ->tooltip(fn (ActivityLog $record): string => $record->description)
                    ->wrap(),

                TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('activity_log_filters')
                    ->label('')
                    ->schema([
                        DatePicker::make('from')
                            ->label('Dari'),
                        DatePicker::make('until')
                            ->label('Sampai'),
                        Select::make('user_id')
                            ->label('Pengguna')
                            ->options(fn (): array => User::query()
                                ->orderBy('full_name')
                                ->pluck('full_name', 'id')
                                ->all())
                            ->searchable()
                            ->preload(),
                        Select::make('action')
                            ->label('Aksi')
                            ->options(fn (): array => ActivityLogQuery::actionFilterOptions())
                            ->searchable(),
                        Select::make('auditable_type')
                            ->label('Tipe Entitas')
                            ->options(fn (): array => ActivityLogQuery::auditableTypeFilterOptions())
                            ->searchable(),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '<=', $date),
                            )
                            ->when(
                                $data['user_id'] ?? null,
                                fn (Builder $query, string $userId): Builder => $query->where('user_id', $userId),
                            )
                            ->when(
                                $data['action'] ?? null,
                                fn (Builder $query, string $action): Builder => $query->where('action', $action),
                            )
                            ->when(
                                $data['auditable_type'] ?? null,
                                fn (Builder $query, string $type): Builder => $query->where('auditable_type', $type),
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

                        if (filled($data['user_id'] ?? null)) {
                            $userName = User::query()->whereKey($data['user_id'])->value('full_name');
                            $indicators[] = Indicator::make('Pengguna: '.($userName ?? $data['user_id']))
                                ->removeField('user_id');
                        }

                        if (filled($data['action'] ?? null)) {
                            $actionLabel = ActivityLogQuery::actionFilterOptions()[$data['action']] ?? $data['action'];
                            $indicators[] = Indicator::make('Aksi: '.$actionLabel)
                                ->removeField('action');
                        }

                        if (filled($data['auditable_type'] ?? null)) {
                            $indicators[] = Indicator::make('Entitas: '.$data['auditable_type'])
                                ->removeField('auditable_type');
                        }

                        return $indicators;
                    }),
            ])
            ->filtersFormColumns(2)
            ->recordActions([
                ViewAction::make(),
            ])
            ->paginated([10, 25, 50]);
    }
}
