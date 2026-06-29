<?php

declare(strict_types=1);

namespace App\Filament\Resources\Rewards\Schemas;

use App\Filament\Support\IndonesianDateTimeFormatter;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RewardInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Reward')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('categoryReward.name')
                            ->label('Kategori'),

                        TextEntry::make('is_active')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                            ->color(fn (bool $state): string => $state ? 'success' : 'danger'),

                        TextEntry::make('name')
                            ->label('Nama reward')
                            ->columnSpanFull(),

                        TextEntry::make('sku')
                            ->label('SKU')
                            ->copyable(),

                        TextEntry::make('points_required')
                            ->label('Poin dibutuhkan')
                            ->numeric(thousandsSeparator: ','),

                        TextEntry::make('start_at')
                            ->label('Mulai berlaku')
                            ->formatStateUsing(fn (mixed $state): ?string => IndonesianDateTimeFormatter::tableDateTooltip($state)),

                        TextEntry::make('end_at')
                            ->label('Berakhir')
                            ->formatStateUsing(fn (mixed $state): ?string => IndonesianDateTimeFormatter::tableDateTooltip($state)),

                        TextEntry::make('description')
                            ->label('Deskripsi')
                            ->html()
                            ->columnSpanFull(),

                        TextEntry::make('created_at')
                            ->label('Dibuat')
                            ->formatStateUsing(fn (mixed $state): ?string => IndonesianDateTimeFormatter::tableDateTooltip($state)),
                    ]),
            ]);
    }
}
