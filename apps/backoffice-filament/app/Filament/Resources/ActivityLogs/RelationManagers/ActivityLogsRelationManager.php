<?php

declare(strict_types=1);

namespace App\Filament\Resources\ActivityLogs\RelationManagers;

use App\Filament\Resources\ActivityLogs\Actions\ViewActivityLogDetailAction;
use App\Filament\Support\IndonesianDateTimeFormatter;
use App\Models\ActivityLog;
use App\Support\ActivityLog\ActivityLogQuery;
use BackedEnum;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

class ActivityLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'auditableActivityLogs';

    protected static ?string $title = 'Riwayat Aktivitas';

    protected static string|BackedEnum|null $icon = 'heroicon-o-clock';

    public function table(Table $table): Table
    {
        return $table
            ->heading(null)
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
                    ->sortable(query: function ($query, string $direction) {
                        return $query->orderBy('action', $direction);
                    }),

                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(80)
                    ->tooltip(fn (ActivityLog $record): string => $record->description)
                    ->wrap(),
            ])
            ->modifyQueryUsing(fn ($query) => $query->with('user'))
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Belum ada riwayat aktivitas')
            ->emptyStateDescription('Belum ada riwayat aktivitas untuk record ini.')
            ->recordActions([
                ViewActivityLogDetailAction::make(),
            ])
            ->headerActions([])
            ->paginated([10, 25, 50]);
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return Gate::check('view', $ownerRecord);
    }
}
