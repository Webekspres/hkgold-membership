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
                            ->defaultImageUrl(fn ($record): string => 'https://ui-avatars.com/api/?name='.urlencode($record->user?->full_name ?? 'Member').'&background=random'),
                        TextEntry::make('user.full_name')
                            ->label('Nama lengkap'),
                        TextEntry::make('member_number')
                            ->label('Nomor member')
                            ->copyable()
                            ->icon(Heroicon::OutlinedIdentification),
                        TextEntry::make('current_tier')
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
                        TextEntry::make('phone_number')
                            ->label('Telepon')
                            ->copyable()
                            ->icon(Heroicon::OutlinedPhone)
                            ->formatStateUsing(fn (?string $state): string => filled($state)
                                ? '+'.ltrim($state, '+')
                                : '—'),
                        TextEntry::make('birth_date')
                            ->label('Tanggal lahir')
                            ->date('d M Y')
                            ->placeholder('—'),
                        TextEntry::make('user.is_active')
                            ->label('Status akun')
                            ->badge()
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                            ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                        TextEntry::make('is_suspended')
                            ->label('Ditangguhkan')
                            ->badge()
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Ya' : 'Tidak')
                            ->color(fn (bool $state): string => $state ? 'danger' : 'success'),
                    ]),
                Section::make('Keanggotaan')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('point_balance')
                            ->label('Saldo poin')
                            ->numeric(),
                        TextEntry::make('highest_point')
                            ->label('Poin tertinggi')
                            ->numeric(),
                        TextEntry::make('registeredBranch.name')
                            ->label('Cabang pendaftaran')
                            ->placeholder('—'),
                        TextEntry::make('address.street')
                            ->label('Alamat')
                            ->placeholder('—')
                            ->columnSpanFull(),
                        TextEntry::make('address.village.nama')
                            ->label('Kelurahan')
                            ->placeholder('—'),
                        TextEntry::make('address.postalCode.kodepos')
                            ->label('Kode pos')
                            ->placeholder('—'),
                        TextEntry::make('last_activity_at')
                            ->label('Aktivitas terakhir')
                            ->dateTime(),
                        TextEntry::make('created_at')
                            ->label('Terdaftar')
                            ->dateTime(),
                    ]),
            ]);
    }
}
