<?php

declare(strict_types=1);

namespace App\Filament\Resources\PointAnnualArchives;

use App\Filament\Resources\PointAnnualArchives\Pages\ListPointAnnualArchives;
use App\Filament\Resources\PointAnnualArchives\Pages\ViewPointAnnualArchive;
use App\Filament\Resources\PointAnnualArchives\Tables\PointAnnualArchivePeriodsTable;
use App\Models\PointAnnualArchivePeriod;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PointAnnualArchiveResource extends Resource
{
    protected static ?string $model = PointAnnualArchivePeriod::class;

    protected static ?string $navigationLabel = 'Arsip Poin';

    protected static ?string $modelLabel = 'Arsip Poin';

    protected static ?string $pluralModelLabel = 'Arsip Poin';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static string|\UnitEnum|null $navigationGroup = 'Loyalty Point';

    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return PointAnnualArchivePeriodsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPointAnnualArchives::route('/'),
            'view' => ViewPointAnnualArchive::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->orderByDesc('archive_year');
    }
}
