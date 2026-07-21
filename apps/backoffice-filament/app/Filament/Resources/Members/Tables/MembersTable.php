<?php

declare(strict_types=1);

namespace App\Filament\Resources\Members\Tables;

use App\Enums\TierStatus;
use App\Filament\Resources\Members\Schemas\MemberForm;
use App\Models\Branch;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class MembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('member_number')
                    ->label('No. Member')
                    ->color('primary')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Nomor member disalin')
                    ->copyMessageDuration(1500),

                TextColumn::make('user.full_name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('phone_number')
                    ->label('No. HP')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn (?string $state): string => filled($state)
                        ? '+'.ltrim($state, '+')
                        : '—')
                    ->toggleable(),

                TextColumn::make('registeredBranch.name')
                    ->label('Cabang')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('point_balance')
                    ->label('Poin')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('current_tier')
                    ->label('Tier')
                    ->badge()
                    ->formatStateUsing(fn (TierStatus $state): string => match ($state) {
                        TierStatus::Silver => 'Silver',
                        TierStatus::Gold => 'Gold',
                        TierStatus::Platinum => 'Platinum',
                        TierStatus::Elite => 'Elite',
                    })
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('user.is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->toggleable(),

                IconColumn::make('is_suspended')
                    ->label('Suspend')
                    ->boolean()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('current_tier')
                    ->label('Tier')
                    ->options(MemberForm::tierOptions()),
                SelectFilter::make('registered_at_branch_id')
                    ->label('Cabang')
                    ->options(fn (): array => Branch::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_suspended')
                    ->label('Ditangguhkan')
                    ->placeholder('Semua')
                    ->trueLabel('Ya')
                    ->falseLabel('Tidak'),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
