<?php

declare(strict_types=1);

namespace App\Filament\Resources\SubDistricts;

use App\Filament\Resources\SubDistricts\Pages\ListSubDistricts;
use App\Filament\Resources\SubDistricts\Schemas\SubDistrictForm;
use App\Filament\Resources\SubDistricts\Tables\SubDistrictsTable;
use App\Models\SubDistrict;
use BackedEnum;
use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class SubDistrictResource extends Resource
{
    protected static ?string $model = SubDistrict::class;

    protected static ?string $navigationLabel = 'Kecamatan';

    protected static ?string $modelLabel = 'Kecamatan';

    protected static ?string $pluralModelLabel = 'Kecamatan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static string|\UnitEnum|null $navigationGroup = 'Master Lokasi';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'nama';

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user !== null && $user->hasRole(Utils::getSuperAdminName());
    }

    public static function form(Schema $schema): Schema
    {
        return SubDistrictForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SubDistrictsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubDistricts::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['city.province']);
    }
}
