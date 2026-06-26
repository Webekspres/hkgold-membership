<?php

declare(strict_types=1);

namespace App\Filament\Resources\Cities;

use App\Filament\Resources\Cities\Pages\ListCities;
use App\Filament\Resources\Cities\Schemas\CityForm;
use App\Filament\Resources\Cities\Tables\CitiesTable;
use App\Models\City;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CityResource extends Resource
{
    protected static ?string $model = City::class;

    protected static ?string $navigationLabel = 'Kota';

    protected static ?string $modelLabel = 'Kota/Kabupaten';

    protected static ?string $pluralModelLabel = 'Kota/Kabupaten';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static string|\UnitEnum|null $navigationGroup = 'Master Lokasi';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'nama';

    public static function form(Schema $schema): Schema
    {
        return CityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CitiesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCities::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('province');
    }
}
