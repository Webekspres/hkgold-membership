<?php

declare(strict_types=1);

namespace App\Filament\Resources\Districts;

use App\Filament\Resources\Districts\Pages\ListDistricts;
use App\Filament\Resources\Districts\Schemas\DistrictForm;
use App\Filament\Resources\Districts\Tables\DistrictsTable;
use App\Models\District;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DistrictResource extends Resource
{
    protected static ?string $model = District::class;

    protected static ?string $navigationLabel = 'Kecamatan';

    protected static ?string $modelLabel = 'Kecamatan';

    protected static ?string $pluralModelLabel = 'Kecamatan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static string|\UnitEnum|null $navigationGroup = 'Master Lokasi';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'nama';

    public static function form(Schema $schema): Schema
    {
        return DistrictForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DistrictsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDistricts::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['regency.province']);
    }
}
