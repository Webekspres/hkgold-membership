<?php

declare(strict_types=1);

namespace App\Filament\Resources\RedeemTokens\Schemas;

use App\Filament\Resources\RedeemTokens\Tables\RedeemTokensTable;
use App\Models\RedeemToken;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class RedeemTokenInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Status Kupon')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('token_code')
                            ->label('Kode Token')
                            ->copyable()
                            ->weight('bold')
                            ->icon(Heroicon::OutlinedTicket),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->state(fn (RedeemToken $record): string => RedeemTokensTable::statusLabel($record))
                            ->color(fn (RedeemToken $record): string => RedeemTokensTable::statusColor($record)),
                        TextEntry::make('held_points')
                            ->label('Poin Ditahan')
                            ->numeric(thousandsSeparator: '.'),
                        TextEntry::make('expired_at')
                            ->label('Kedaluwarsa')
                            ->dateTime('d M Y H:i'),
                        TextEntry::make('created_at')
                            ->label('Dibuat')
                            ->dateTime('d M Y H:i'),
                    ]),
                Section::make('Member')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('member.user.full_name')
                            ->label('Nama')
                            ->placeholder('-'),
                        TextEntry::make('member.member_number')
                            ->label('Nomor Member')
                            ->copyable()
                            ->placeholder('-'),
                        TextEntry::make('member.phone_number')
                            ->label('No. HP')
                            ->copyable()
                            ->placeholder('-'),
                    ]),
                Section::make('Reward')
                    ->columns(3)
                    ->schema([
                        ImageEntry::make('reward.rewardImages.0.media.file_url')
                            ->label('Gambar')
                            ->square()
                            ->placeholder('-'),
                        TextEntry::make('reward.name')
                            ->label('Nama Reward')
                            ->placeholder('-'),
                        TextEntry::make('reward.sku')
                            ->label('SKU')
                            ->placeholder('-'),
                    ]),
                Section::make('Cabang')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('branch.name')
                            ->label('Nama Cabang')
                            ->placeholder('-'),
                        TextEntry::make('branch.address')
                            ->label('Alamat')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
