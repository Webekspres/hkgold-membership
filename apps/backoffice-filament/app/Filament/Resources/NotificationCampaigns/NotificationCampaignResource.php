<?php

declare(strict_types=1);

namespace App\Filament\Resources\NotificationCampaigns;

use App\Filament\Resources\NotificationCampaigns\Pages\ListNotificationCampaigns;
use App\Filament\Resources\NotificationCampaigns\Pages\ViewNotificationCampaign;
use App\Filament\Resources\NotificationCampaigns\Schemas\NotificationCampaignInfolist;
use App\Filament\Resources\NotificationCampaigns\Tables\NotificationCampaignsTable;
use App\Models\NotificationCampaign;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class NotificationCampaignResource extends Resource
{
    protected static ?string $model = NotificationCampaign::class;

    protected static ?string $navigationLabel = 'Kampanye Notifikasi';

    protected static ?string $modelLabel = 'Kampanye Notifikasi';

    protected static ?string $pluralModelLabel = 'Kampanye Notifikasi';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static string|\UnitEnum|null $navigationGroup = 'Notifikasi';

    protected static ?int $navigationSort = 2;

    public static function infolist(Schema $schema): Schema
    {
        return NotificationCampaignInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NotificationCampaignsTable::configure($table);
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
            'index' => ListNotificationCampaigns::route('/'),
            'view' => ViewNotificationCampaign::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['createdBy'])
            ->orderByDesc('created_at');
    }
}
