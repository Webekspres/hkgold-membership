<?php

declare(strict_types=1);

namespace App\Filament\Resources\TierMembers\Schemas;

use App\Enums\TierStatus;
use App\Filament\Resources\TierMembers\Support\TierMemberFormSupport;
use App\Filament\Resources\TierMembers\Support\TierSupport;
use App\Filament\Support\IndonesianDateTimeFormatter;
use App\Models\TierMember;
use App\Models\TransactionType;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TierMemberInfolist
{
    public static function configure(Schema $schema): Schema
    {
        $conversionEntries = TransactionType::orderBy('id')->get()
            ->map(function (TransactionType $type): TextEntry {
                return TextEntry::make(TierMemberFormSupport::conversionFieldKey($type->type_key))
                    ->label($type->display_name)
                    ->prefix('Rp ')
                    ->numeric(thousandsSeparator: ',')
                    ->getStateUsing(function (TierMember $record) use ($type): ?string {
                        $record->loadMissing('conversionRules.transactionType');

                        $nominal = $record->conversionRules
                            ->firstWhere('transaction_type_id', $type->id)
                            ?->conversion_nominal;

                        return $nominal !== null ? (string) $nominal : null;
                    })
                    ->placeholder('—');
            })
            ->all();

        return $schema
            ->components([
                Section::make('Range Poin')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('tier_code')
                            ->label('Tier')
                            ->badge()
                            ->formatStateUsing(fn (TierStatus $state): string => TierSupport::label($state))
                            ->color(fn (TierStatus $state): string => TierSupport::color($state))
                            ->columnSpanFull(),

                        TextEntry::make('min_points')
                            ->label('Min Poin')
                            ->numeric(thousandsSeparator: ','),

                        TextEntry::make('max_points')
                            ->label('Max Poin')
                            ->numeric(thousandsSeparator: ','),
                    ]),

                Section::make('Konversi Poin')
                    ->description('Nominal transaksi (Rp) yang dibutuhkan untuk mendapatkan 1 poin')
                    ->columns(2)
                    ->schema($conversionEntries),

                Section::make('Benefit')
                    ->schema([
                        RepeatableEntry::make('tierBenefits')
                            ->label('Daftar Benefit')
                            ->schema([
                                TextEntry::make('title')
                                    ->label('Benefit'),
                                TextEntry::make('description')
                                    ->label('Keterangan'),
                            ])
                            ->columns(2)
                            ->placeholder('Belum ada benefit')
                            ->columnSpanFull(),
                    ]),

                Section::make('Metadata')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Dibuat')
                            ->formatStateUsing(fn (mixed $state): ?string => IndonesianDateTimeFormatter::tableDateTooltip($state)),

                        TextEntry::make('updated_at')
                            ->label('Diperbarui')
                            ->formatStateUsing(fn (mixed $state): ?string => IndonesianDateTimeFormatter::tableDateTooltip($state)),
                    ]),
            ]);
    }
}
