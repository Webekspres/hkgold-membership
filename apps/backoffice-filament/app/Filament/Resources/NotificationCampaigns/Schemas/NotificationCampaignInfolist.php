<?php

declare(strict_types=1);

namespace App\Filament\Resources\NotificationCampaigns\Schemas;

use App\Filament\Resources\NotificationCampaigns\Support\BroadcastNotificationFormSupport;
use App\Models\NotificationCampaign;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class NotificationCampaignInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Konten Kampanye')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('title')
                            ->label('Judul')
                            ->columnSpanFull(),
                        TextEntry::make('body')
                            ->label('Isi')
                            ->columnSpanFull(),
                        TextEntry::make('platforms')
                            ->label('Platform')
                            ->formatStateUsing(fn (?array $state): string => BroadcastNotificationFormSupport::formatPlatformLabels($state ?? [])),
                    ]),
                Section::make('Audience & Statistik')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('criteria_json')
                            ->label('Audience')
                            ->formatStateUsing(fn (?array $state): string => BroadcastNotificationFormSupport::audienceLabel($state ?? [])),
                        TextEntry::make('targeted_count')
                            ->label('Ditarget')
                            ->numeric(thousandsSeparator: '.'),
                        TextEntry::make('accepted_count')
                            ->label('Diterima Provider')
                            ->numeric(thousandsSeparator: '.')
                            ->placeholder('—'),
                        TextEntry::make('failed_count')
                            ->label('Gagal')
                            ->numeric(thousandsSeparator: '.')
                            ->placeholder('—'),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn (NotificationCampaign $record): string => BroadcastNotificationFormSupport::statusLabel($record->status))
                            ->color(fn (NotificationCampaign $record): string => BroadcastNotificationFormSupport::statusColor($record->status)),
                        TextEntry::make('error_message')
                            ->label('Pesan Error')
                            ->placeholder('—')
                            ->visible(fn (NotificationCampaign $record): bool => $record->error_message !== null)
                            ->columnSpanFull(),
                    ]),
                Section::make('Metadata')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('createdBy.full_name')
                            ->label('Dibuat Oleh')
                            ->placeholder('—'),
                        TextEntry::make('created_at')
                            ->label('Dibuat Pada')
                            ->dateTime('d M Y H:i'),
                        TextEntry::make('updated_at')
                            ->label('Diperbarui Pada')
                            ->dateTime('d M Y H:i'),
                    ]),
            ]);
    }
}
