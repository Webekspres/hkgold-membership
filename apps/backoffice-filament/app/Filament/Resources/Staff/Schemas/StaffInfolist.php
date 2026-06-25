<?php

declare(strict_types=1);

namespace App\Filament\Resources\Staff\Schemas;

use App\Enums\Role;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class StaffInfolist
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
                            ->defaultImageUrl(fn ($record): string => 'https://ui-avatars.com/api/?name='.urlencode($record->user?->full_name ?? 'Staff').'&background=random'),
                        TextEntry::make('user.full_name')
                            ->label('Nama lengkap'),
                        TextEntry::make('employee_code')
                            ->label('Kode karyawan')
                            ->copyable()
                            ->icon(Heroicon::OutlinedIdentification),
                        TextEntry::make('user.role')
                            ->label('Role')
                            ->badge()
                            ->formatStateUsing(fn (Role $state): string => self::roleLabel($state)),
                    ]),
                Section::make('Kontak & Akun')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('user.email')
                            ->label('Email')
                            ->copyable()
                            ->icon(Heroicon::OutlinedEnvelope),
                        TextEntry::make('user.is_active')
                            ->label('Status akun')
                            ->badge()
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                            ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                    ]),
                Section::make('Penempatan')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('branch.name')
                            ->label('Cabang')
                            ->placeholder('—'),
                        TextEntry::make('branch.branch_code')
                            ->label('Kode cabang')
                            ->placeholder('—'),
                    ]),
                Section::make('Metadata')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Terdaftar')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label('Diperbarui')
                            ->dateTime(),
                    ]),
            ]);
    }

    public static function roleLabel(Role $role): string
    {
        return match ($role) {
            Role::Administrator => 'Administrator',
            Role::SuperAdmin => 'Super Admin',
            Role::Marketing => 'Marketing',
            Role::StoreManager => 'Store Manager',
            Role::Member => 'Member',
        };
    }
}
