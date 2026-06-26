<?php

declare(strict_types=1);

namespace App\Filament\Resources\Branches\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class BranchInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Data Cabang')
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        TextEntry::make('branch_code')
                            ->label('Kode cabang')
                            ->copyable()
                            ->icon(Heroicon::OutlinedIdentification),
                        TextEntry::make('name')
                            ->label('Nama cabang'),
                        TextEntry::make('phone')
                            ->label('Telepon')
                            ->placeholder('—')
                            ->copyable()
                            ->icon(Heroicon::OutlinedPhone)
                            ->formatStateUsing(fn (?string $state): string => filled($state)
                                ? '+'.ltrim($state, '+')
                                : '—'),
                        IconEntry::make('is_online_warehouse')
                            ->label('Gudang online')
                            ->boolean(),
                        TextEntry::make('normalizedAddress.street')
                            ->label('Alamat jalan')
                            ->placeholder('—')
                            ->columnSpanFull(),
                        TextEntry::make('normalizedAddress.village.nama')
                            ->label('Kelurahan')
                            ->placeholder('—'),
                        TextEntry::make('normalizedAddress.village.subDistrict.nama')
                            ->label('Kecamatan')
                            ->placeholder('—'),
                        TextEntry::make('normalizedAddress.village.subDistrict.city.nama')
                            ->label('Kota/Kabupaten')
                            ->placeholder('—'),
                        TextEntry::make('normalizedAddress.village.subDistrict.city.province.nama')
                            ->label('Provinsi')
                            ->placeholder('—'),
                        TextEntry::make('normalizedAddress.postalCode.kodepos')
                            ->label('Kode pos')
                            ->placeholder('—'),
                    ]),
            ]);
    }
}
