<?php

declare(strict_types=1);

namespace App\Filament\Resources\Members\Tables;

use App\Enums\TierStatus as EnumsTierStatus;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class MembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('member_code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Kode member disalin')
                    ->copyMessageDuration(1500),

                TextColumn::make('user.name')
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

                TextColumn::make('user.phone')
                    ->label('No. HP')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn (?string $state): string => filled($state)
                        ? '+'.ltrim($state, '+')
                        : '—'),

                TextColumn::make('total_points')
                    ->label('Poin')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                TextColumn::make('tier')
                    ->label('Tier')
                    ->badge()
                    ->formatStateUsing(fn (EnumsTierStatus $state): string => match ($state) {
                        EnumsTierStatus::Silver => 'Silver',
                        EnumsTierStatus::Gold => 'Gold',
                        EnumsTierStatus::Platinum => 'Platinum',
                        EnumsTierStatus::Sapphire => 'Sapphire',
                    })
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created at')
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
