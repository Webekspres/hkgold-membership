<?php

declare(strict_types=1);

namespace App\Filament\Resources\Members\Tables;

use App\Enums\TierStatus as EnumsTierStatus;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class MembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('member_number')
                    ->label('No. Member')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Nomor member disalin')
                    ->copyMessageDuration(1500),

                TextColumn::make('user.full_name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->html()
                    ->formatStateUsing(function ($record, $state): string {
                        $name = e($state ?? '');
                        $avatarUrl = e(
                            $record->user?->profilePhoto?->file_url
                            ?? 'https://ui-avatars.com/api/?name='.urlencode($state ?? 'Member').'&background=random'
                        );

                        return <<<HTML
                            <div class="flex items-center gap-3">
                                <img src="{$avatarUrl}" class="h-10 w-10 shrink-0 rounded-full border border-gray-200 object-cover" alt="Avatar">
                                <span class="font-semibold">{$name}</span>
                            </div>
                            HTML;
                    }),

                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone_number')
                    ->label('No. HP')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn (?string $state): string => filled($state)
                        ? '+'.ltrim($state, '+')
                        : '—'),

                TextColumn::make('registeredBranch.name')
                    ->label('Cabang')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('point_balance')
                    ->label('Poin')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('current_tier')
                    ->label('Tier')
                    ->badge()
                    ->formatStateUsing(fn (EnumsTierStatus $state): string => match ($state) {
                        EnumsTierStatus::Silver => 'Silver',
                        EnumsTierStatus::Gold => 'Gold',
                        EnumsTierStatus::Platinum => 'Platinum',
                        EnumsTierStatus::Sapphire => 'Sapphire',
                    })
                    ->sortable(),

                IconColumn::make('user.is_active')
                    ->label('Aktif')
                    ->boolean(),

                IconColumn::make('is_suspended')
                    ->label('Suspend')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
