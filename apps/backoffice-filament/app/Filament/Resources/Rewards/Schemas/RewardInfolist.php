<?php

declare(strict_types=1);

namespace App\Filament\Resources\Rewards\Schemas;

use App\Filament\Support\IndonesianDateTimeFormatter;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

class RewardInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make()
                    ->heading(null)
                    ->schema([
                        View::make('filament.resources.rewards.partials.reward-view-header')
                            ->columnSpanFull(),

                        TextEntry::make('sku')
                            ->label('SKU')
                            ->copyable()
                            ->columnSpanFull(),

                        TextEntry::make('start_at')
                            ->label('Mulai berlaku')
                            ->formatStateUsing(fn (mixed $state): ?string => IndonesianDateTimeFormatter::tableDateTooltip($state)),

                        TextEntry::make('end_at')
                            ->label('Berakhir')
                            ->formatStateUsing(fn (mixed $state): ?string => IndonesianDateTimeFormatter::tableDateTooltip($state)),

                        TextEntry::make('created_at')
                            ->label('Dibuat')
                            ->formatStateUsing(fn (mixed $state): ?string => IndonesianDateTimeFormatter::tableDateTooltip($state)),

                        TextEntry::make('updated_at')
                            ->label('Diperbarui')
                            ->formatStateUsing(fn (mixed $state): ?string => IndonesianDateTimeFormatter::tableDateTooltip($state)),

                        TextEntry::make('description')
                            ->label('Deskripsi')
                            ->html()
                            ->columnSpanFull(),
                    ])
                    ->columns(4),

                Section::make('Galeri Gambar')
                    ->schema([
                        View::make('filament.resources.rewards.partials.reward-gallery')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
            ]);
    }
}
