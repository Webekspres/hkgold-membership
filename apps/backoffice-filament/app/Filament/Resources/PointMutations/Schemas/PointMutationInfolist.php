<?php

declare(strict_types=1);

namespace App\Filament\Resources\PointMutations\Schemas;

use App\Filament\Resources\PointMutations\Support\PointMutationSupport;
use App\Filament\Support\IndonesianDateTimeFormatter;
use App\Models\PointMutation;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PointMutationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Transaksi')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('transaction_date')
                            ->label('Tanggal Transaksi')
                            ->formatStateUsing(fn (mixed $state): ?string => IndonesianDateTimeFormatter::tableDate($state))
                            ->tooltip(fn (mixed $state): ?string => IndonesianDateTimeFormatter::tableDateTooltip($state)),
                        TextEntry::make('transactionType.display_name')
                            ->label('Tipe Transaksi')
                            ->placeholder('—'),
                        TextEntry::make('purchase_nominal')
                            ->label('Nominal Pembelian')
                            ->prefix('Rp ')
                            ->numeric(decimalPlaces: 0, thousandsSeparator: ','),
                        TextEntry::make('points_delta')
                            ->label('Perubahan Poin')
                            ->state(fn (PointMutation $record): string => PointMutationSupport::formatPointsDelta($record)['formatted'])
                            ->badge()
                            ->color(fn (PointMutation $record): string => PointMutationSupport::formatPointsDelta($record)['color']),
                        TextEntry::make('balance_snapshot')
                            ->label('Sisa Balance')
                            ->numeric(thousandsSeparator: ','),
                        TextEntry::make('receipt_number')
                            ->label('No. Struk')
                            ->placeholder('—')
                            ->copyable(),
                    ]),
                Section::make('Member & Cabang')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('member.member_number')
                            ->label('No. Member')
                            ->copyable(),
                        TextEntry::make('member.user.full_name')
                            ->label('Nama Member')
                            ->placeholder('—'),
                        TextEntry::make('branch.name')
                            ->label('Cabang')
                            ->placeholder('—'),
                        TextEntry::make('uploaded_at')
                            ->label('Diunggah')
                            ->dateTime('d M Y, H:i')
                            ->placeholder('—'),
                    ]),
            ]);
    }
}
