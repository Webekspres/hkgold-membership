<?php

declare(strict_types=1);

namespace App\Filament\Resources\TierMembers\Tables;

use App\Enums\TierStatus;
use App\Filament\Resources\TierMembers\Schemas\TierMemberForm;
use App\Filament\Resources\TierMembers\Schemas\TierMemberInfolist;
use App\Filament\Resources\TierMembers\Support\TierMemberFormSupport;
use App\Filament\Resources\TierMembers\Support\TierSupport;
use App\Models\TierMember;
use App\Models\TransactionType;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TierMembersTable
{
    public static function configure(Table $table): Table
    {
        $conversionColumns = TransactionType::orderBy('id')->get()
            ->map(function (TransactionType $type): TextColumn {
                return TextColumn::make(TierMemberFormSupport::conversionFieldKey($type->type_key))
                    ->label($type->display_name)
                    ->getStateUsing(function (TierMember $record) use ($type): ?string {
                        $rule = $record->conversionRules
                            ->firstWhere('transaction_type_id', $type->id);

                        return $rule ? (string) $rule->conversion_nominal : null;
                    })
                    ->formatStateUsing(fn (?string $state): string => $state !== null
                        ? 'Rp ' . number_format((float) $state, 0, '.', ',')
                        : ''
                    )
                    ->badge()
                    ->color(fn (?string $state): string => $state !== null ? 'success' : 'warning')
                    ->placeholder('Belum diset');
            })
            ->all();

        return $table
            ->columns([
                TextColumn::make('tier_code')
                    ->label('Kode')
                    ->badge()
                    ->formatStateUsing(fn (TierStatus $state): string => TierSupport::label($state))
                    ->color(fn (TierStatus $state): string => TierSupport::color($state)),

                TextColumn::make('min_points')
                    ->label('Min Poin')
                    ->numeric(thousandsSeparator: ',')
                    ->sortable(),

                TextColumn::make('max_points')
                    ->label('Max Poin')
                    ->numeric(thousandsSeparator: ',')
                    ->sortable(),

                ...$conversionColumns,
            ])
            ->filters([])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Lihat')
                        ->modalHeading(fn (TierMember $record): string => TierSupport::label($record->tier_code))
                        ->modalWidth(Width::TwoExtraLarge)
                        ->infolist(fn ($infolist) => TierMemberInfolist::configure($infolist)),

                    EditAction::make()
                        ->label('Edit')
                        ->modalHeading(fn (TierMember $record): string => TierSupport::label($record->tier_code))
                        ->modalWidth(Width::TwoExtraLarge)
                        ->form(fn ($form) => TierMemberForm::configure($form))
                        ->fillForm(fn (TierMember $record): array => TierMemberFormSupport::fillFormData($record))
                        ->using(fn (TierMember $record, array $data): TierMember => TierMemberFormSupport::saveWithConversions($record, $data)),
                ]),
            ]);
    }
}
