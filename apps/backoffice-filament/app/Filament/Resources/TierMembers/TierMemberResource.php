<?php

declare(strict_types=1);

namespace App\Filament\Resources\TierMembers;

use App\Filament\Resources\TierMembers\Pages\ListTierMembers;
use App\Filament\Resources\TierMembers\Schemas\TierMemberForm;
use App\Filament\Resources\TierMembers\Schemas\TierMemberInfolist;
use App\Filament\Resources\TierMembers\Tables\TierMembersTable;
use App\Models\TierMember;
use BackedEnum;
use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TierMemberResource extends Resource
{
    protected static ?string $model = TierMember::class;

    protected static ?string $navigationLabel = 'Manajemen Tier';

    protected static ?string $modelLabel = 'Tier';

    protected static ?string $pluralModelLabel = 'Manajemen Tier';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static string|\UnitEnum|null $navigationGroup = 'Konfigurasi';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'tier_code';

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user !== null && $user->hasRole(Utils::getSuperAdminName());
    }

    public static function form(Schema $schema): Schema
    {
        return TierMemberForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TierMemberInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TierMembersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTierMembers::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['conversionRules.transactionType', 'tierBenefits'])
            ->orderByRaw("FIELD(tier_code, 'SILVER', 'GOLD', 'PLATINUM', 'ELITE')");
    }
}
