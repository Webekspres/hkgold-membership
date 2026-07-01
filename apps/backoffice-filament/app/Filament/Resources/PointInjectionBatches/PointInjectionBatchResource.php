<?php

declare(strict_types=1);

namespace App\Filament\Resources\PointInjectionBatches;

use App\Filament\Resources\PointInjectionBatches\Tables\PointInjectionBatchesTable;
use App\Models\PointInjectionBatch;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PointInjectionBatchResource extends Resource
{
    protected static ?string $model = PointInjectionBatch::class;

    protected static ?string $navigationLabel = 'Update Masal';

    protected static ?string $modelLabel = 'Update Masal';

    protected static ?string $pluralModelLabel = 'Update Masal';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    protected static string|\UnitEnum|null $navigationGroup = 'Loyalty Point';

    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return PointInjectionBatchesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPointInjectionBatches::route('/'),
            'view' => Pages\ViewPointInjectionBatch::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['staff.user', 'media'])
            ->orderByDesc('uploaded_at');
    }
}
