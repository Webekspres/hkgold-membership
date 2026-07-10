<?php

declare(strict_types=1);

namespace App\Filament\Resources\Members\Schemas;

use App\Enums\TierStatus;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class MemberInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Profil')
                    ->columns(3)
                    ->schema([
                        ImageEntry::make('user.profilePhoto.file_url')
                            ->label('Foto profil')
                            ->circular()
                            ->defaultImageUrl(fn (): string => 'https://ui-avatars.com/api/?name=Member&background=random'),
                        TextEntry::make('user.name')
                            ->label('Nama'),
                        TextEntry::make('member_code')
                            ->label('Kode member')
                            ->copyable()
                            ->icon(Heroicon::OutlinedIdentification),
                        TextEntry::make('tier')
                            ->label('Tier')
                            ->badge()
                            ->formatStateUsing(fn (TierStatus $state): string => match ($state) {
                                TierStatus::Silver => 'Silver',
                                TierStatus::Gold => 'Gold',
                                TierStatus::Platinum => 'Platinum',
                                TierStatus::Sapphire => 'Sapphire',
                            }),
                    ]),
                Section::make('Kontak & Akun')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('user.email')
                            ->label('Email')
                            ->copyable()
                            ->icon(Heroicon::OutlinedEnvelope),
                        TextEntry::make('user.phone')
                            ->label('Telepon')
                            ->copyable()
                            ->icon(Heroicon::OutlinedPhone),
                        TextEntry::make('user.is_active')
                            ->label('Status akun')
                            ->badge()
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                            ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                        TextEntry::make('phone_change_pending')
                            ->label('Perubahan telepon tertunda')
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Ya' : 'Tidak'),
                    ]),
                Section::make('Keanggotaan')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('total_points')
                            ->label('Total poin')
                            ->numeric(decimalPlaces: 2),
                        TextEntry::make('dob')
                            ->label('Tanggal lahir')
                            ->date(),
                        TextEntry::make('address.street')
                            ->label('Alamat')
                            ->placeholder('—'),
                        TextEntry::make('created_at')
                            ->label('Terdaftar')
                            ->dateTime(),
                    ]),
            ]);
    }
}
