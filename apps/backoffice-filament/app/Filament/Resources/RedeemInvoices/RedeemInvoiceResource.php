<?php

declare(strict_types=1);

namespace App\Filament\Resources\RedeemInvoices;

use App\Filament\Resources\RedeemInvoices\Pages\ListRedeemInvoices;
use App\Filament\Resources\RedeemInvoices\Pages\ViewRedeemInvoice;
use App\Filament\Resources\RedeemInvoices\Tables\RedeemInvoicesTable;
use App\Models\RedeemInvoice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RedeemInvoiceResource extends Resource
{
    protected static ?string $model = RedeemInvoice::class;

    protected static ?string $navigationLabel = 'Redeem Invoice';

    protected static ?string $modelLabel = 'Redeem Invoice';

    protected static ?string $pluralModelLabel = 'Redeem Invoice';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    protected static string|\UnitEnum|null $navigationGroup = 'Redeem Poin';

    protected static ?int $navigationSort = 1;

    public static function table(Table $table): Table
    {
        return RedeemInvoicesTable::configure($table);
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
            'index' => ListRedeemInvoices::route('/'),
            'view' => ViewRedeemInvoice::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'member.user.profilePhoto',
                'branch',
                'staff.user',
                'reward.categoryReward',
                'reward.rewardImages.media',
            ])
            ->orderByDesc('created_at');
    }
}
