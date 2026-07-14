<?php

declare(strict_types=1);

namespace App\Filament\Resources\Villages;

use App\Filament\Resources\Villages\Pages\ListVillages;
use App\Filament\Resources\Villages\Schemas\VillageForm;
use App\Filament\Resources\Villages\Tables\VillagesTable;
use App\Models\Village;
use BackedEnum;
use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class VillageResource extends Resource
{
    protected static ?string $model = Village::class;

    protected static ?string $navigationLabel = 'Kelurahan';

    protected static ?string $modelLabel = 'Kelurahan';

    protected static ?string $pluralModelLabel = 'Kelurahan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static string|\UnitEnum|null $navigationGroup = 'Master Lokasi';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'nama';

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user !== null && $user->hasRole(Utils::getSuperAdminName());
    }

    public static function form(Schema $schema): Schema
    {
        return VillageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VillagesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVillages::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['subDistrict.city.province']);
    }
}
