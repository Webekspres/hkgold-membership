<?php

declare(strict_types=1);

namespace App\Filament\Resources\RedeemTokens;

use App\Filament\Resources\RedeemTokens\Pages\ListRedeemTokens;
use App\Filament\Resources\RedeemTokens\Pages\ViewRedeemToken;
use App\Filament\Resources\RedeemTokens\Schemas\RedeemTokenInfolist;
use App\Filament\Resources\RedeemTokens\Tables\RedeemTokensTable;
use App\Models\RedeemToken;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RedeemTokenResource extends Resource
{
    protected static ?string $model = RedeemToken::class;

    protected static ?string $navigationLabel = 'Antrean Kupon';

    protected static ?string $modelLabel = 'Kupon Redeem';

    protected static ?string $pluralModelLabel = 'Antrean Kupon';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static string|\UnitEnum|null $navigationGroup = 'Redeem Poin';

    protected static ?int $navigationSort = 0;

    public static function infolist(Schema $schema): Schema
    {
        return RedeemTokenInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RedeemTokensTable::configure($table);
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
            'index' => ListRedeemTokens::route('/'),
            'view' => ViewRedeemToken::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'member.user',
                'reward.rewardImages.media',
                'branch',
            ])
            ->orderByDesc('created_at');
    }
}
