<?php

declare(strict_types=1);

namespace App\Filament\Resources\Branches\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class BranchInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        View::make('filament.resources.branches.partials.branch-view-header')
                            ->columnSpanFull(),
                        TextEntry::make('phone')
                            ->label('Telepon')
                            ->placeholder('—')
                            ->copyable()
                            ->icon(Heroicon::OutlinedPhone)
                            ->formatStateUsing(fn (?string $state): string => filled($state)
                                ? '+'.ltrim($state, '+')
                                : '—'),
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
                        TextEntry::make('normalizedAddress.street')
                            ->label('Alamat')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
