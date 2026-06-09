<?php

declare(strict_types=1);

namespace App\Filament\Tables;

use App\Enums\TierStatus as EnumsTierStatus;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;

class MembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('member_code')
                    ->label('Kode member')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                // PERBAIKAN: Menggabungkan Avatar dan Nama ke dalam 1 kolom menggunakan Stack Horizontal
                TextColumn::make('user.name')
                    ->label('Member')
                    ->weight(FontWeight::SemiBold)
                    ->searchable()
                    ->description(fn($record): ?string => $record->member_code ? null : null) // Opsional jika butuh sub-text
                    // Gunakan metode prepended untuk menyelipkan Avatar bulat sebelum teks Nama
                    ->prependView('filament.tables.columns.avatar-wrapper', [
                        'image' => fn($record) => $record->user?->profilePhoto?->file_url
                    ])
                    // --- ATAU CARA FILAMENT STANDARD TANPA CUSTOM VIEW (REKOMENDASI): ---
                    ->html()
                    ->formatStateUsing(function ($record, $state) {
                        $avatarUrl = $record->user?->profilePhoto?->file_url
                            ?? 'https://ui-avatars.com/api/?name=' . urlencode($state) . '&background=random';
                        return "
                            <div class='flex items-center gap-3'>
                                <img src='{$avatarUrl}' class='w-10 h-10 rounded-full object-cover border border-gray-200' alt='Avatar'>
                                <span class='font-semibold'>{$state}</span>
                            </div>
                        ";
                    }),

                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('user.phone')
                    ->label('Telepon')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('total_points')
                    ->label('Total poin')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('tier')
                    ->label('Tier')
                    ->badge()
                    ->formatStateUsing(fn(EnumsTierStatus $state): string => match ($state) {
                        EnumsTierStatus::Silver => 'Silver',
                        EnumsTierStatus::Gold => 'Gold',
                        EnumsTierStatus::Platinum => 'Platinum',
                        EnumsTierStatus::Sapphire => 'Sapphire',
                    })
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false), // Jangan disembunyikan agar tabel terisi proporsional

                TextColumn::make('dob')
                    ->label('Tanggal lahir')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->columnManager();
    }
}
