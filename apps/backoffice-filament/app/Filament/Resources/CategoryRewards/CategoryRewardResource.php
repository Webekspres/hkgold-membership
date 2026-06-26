<?php

declare(strict_types=1);

namespace App\Filament\Resources\CategoryRewards;

use App\Filament\Resources\CategoryRewards\Pages\ListCategoryRewards;
use App\Filament\Resources\CategoryRewards\Schemas\CategoryRewardForm;
use App\Filament\Resources\CategoryRewards\Tables\CategoryRewardsTable;
use App\Models\CategoryReward;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CategoryRewardResource extends Resource
{
    protected static ?string $model = CategoryReward::class;

    protected static ?string $navigationLabel = 'Kategori Reward';

    protected static ?string $modelLabel = 'Kategori Reward';

    protected static ?string $pluralModelLabel = 'Kategori Reward';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|\UnitEnum|null $navigationGroup = 'Katalog Reward';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CategoryRewardForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CategoryRewardsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCategoryRewards::route('/'),
        ];
    }
}
