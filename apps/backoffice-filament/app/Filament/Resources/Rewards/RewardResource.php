<?php

declare(strict_types=1);

namespace App\Filament\Resources\Rewards;

use App\Filament\Resources\Rewards\Pages\CreateReward;
use App\Filament\Resources\Rewards\Pages\EditReward;
use App\Filament\Resources\Rewards\Pages\ListRewards;
use App\Filament\Resources\Rewards\Pages\ViewReward;
use App\Filament\Resources\Rewards\Schemas\RewardForm;
use App\Filament\Resources\Rewards\Schemas\RewardInfolist;
use App\Filament\Resources\Rewards\Tables\RewardsTable;
use App\Models\Reward;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RewardResource extends Resource
{
    protected static ?string $model = Reward::class;

    protected static ?string $navigationLabel = 'Katalog';

    protected static ?string $modelLabel = 'Reward';

    protected static ?string $pluralModelLabel = 'Katalog';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGift;

    protected static string|\UnitEnum|null $navigationGroup = 'Katalog Reward';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return RewardForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RewardInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RewardsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRewards::route('/'),
            'create' => CreateReward::route('/create'),
            'view' => ViewReward::route('/{record}'),
            'edit' => EditReward::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['categoryReward'])
            ->withSum('branchStocks', 'actual_stock')
            ->withSum('branchStocks', 'held_stock');
    }
}
