<?php

declare(strict_types=1);

namespace App\Filament\Resources\PointMutations;

use App\Filament\Resources\PointMutations\Pages\ListPointMutations;
use App\Filament\Resources\PointMutations\Tables\PointMutationsTable;
use App\Models\PointMutation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PointMutationResource extends Resource
{
    protected static ?string $model = PointMutation::class;

    protected static ?string $navigationLabel = 'Mutasi Poin';

    protected static ?string $modelLabel = 'Mutasi Poin';

    protected static ?string $pluralModelLabel = 'Mutasi Poin';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static string|\UnitEnum|null $navigationGroup = 'Loyalty Point';

    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return PointMutationsTable::configure($table);
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
            'index' => ListPointMutations::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['member.user', 'branch', 'transactionType'])
            ->orderByDesc('transaction_date');
    }
}
