<?php

declare(strict_types=1);

namespace App\Filament\Resources\PostalCodes;

use App\Filament\Resources\PostalCodes\Pages\ListPostalCodes;
use App\Filament\Resources\PostalCodes\Schemas\PostalCodeForm;
use App\Filament\Resources\PostalCodes\Tables\PostalCodesTable;
use App\Models\PostalCode;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PostalCodeResource extends Resource
{
    protected static ?string $model = PostalCode::class;

    protected static ?string $navigationLabel = 'Kode Pos';

    protected static ?string $modelLabel = 'Kode Pos';

    protected static ?string $pluralModelLabel = 'Kode Pos';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInbox;

    protected static string|\UnitEnum|null $navigationGroup = 'Master Lokasi';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'code';

    public static function form(Schema $schema): Schema
    {
        return PostalCodeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PostalCodesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPostalCodes::route('/'),
        ];
    }
}
