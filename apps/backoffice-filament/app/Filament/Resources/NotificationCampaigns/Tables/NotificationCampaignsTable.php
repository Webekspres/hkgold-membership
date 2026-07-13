<?php

declare(strict_types=1);

namespace App\Filament\Resources\NotificationCampaigns\Tables;

use App\Enums\CampaignStatus;
use App\Filament\Resources\NotificationCampaigns\Support\BroadcastNotificationFormSupport;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class NotificationCampaignsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (CampaignStatus $state): string => BroadcastNotificationFormSupport::statusLabel($state))
                    ->color(fn (CampaignStatus $state): string => BroadcastNotificationFormSupport::statusColor($state))
                    ->sortable(),

                TextColumn::make('targeted_count')
                    ->label('Ditarget')
                    ->numeric(thousandsSeparator: '.')
                    ->sortable(),

                TextColumn::make('accepted_count')
                    ->label('Diterima')
                    ->numeric(thousandsSeparator: '.')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('failed_count')
                    ->label('Gagal')
                    ->numeric(thousandsSeparator: '.')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('createdBy.full_name')
                    ->label('Pembuat')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        CampaignStatus::Pending->value => 'Menunggu',
                        CampaignStatus::Processing->value => 'Diproses',
                        CampaignStatus::Completed->value => 'Selesai',
                        CampaignStatus::Failed->value => 'Gagal',
                    ])
                    ->native(false),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
            ])
            ->emptyStateHeading('Belum ada kampanye')
            ->emptyStateDescription('Kampanye broadcast akan muncul setelah Anda mengirim notifikasi massal.');
    }
}
