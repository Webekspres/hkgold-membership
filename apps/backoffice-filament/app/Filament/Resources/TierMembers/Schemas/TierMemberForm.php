<?php

declare(strict_types=1);

namespace App\Filament\Resources\TierMembers\Schemas;

use App\Filament\Resources\TierMembers\Support\TierMemberFormSupport;
use App\Filament\Resources\TierMembers\Support\TierSupport;
use App\Models\TierMember;
use App\Models\TransactionType;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class TierMemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Range Poin')
                    ->columns(2)
                    ->schema([
                        TextInput::make('min_points')
                            ->label('Min Poin')
                            ->required()
                            ->integer()
                            ->minValue(0)
                            ->numeric()
                            ->validationMessages([
                                'required' => 'Min poin wajib diisi.',
                                'integer'  => 'Min poin harus berupa bilangan bulat.',
                                'min'      => 'Min poin tidak boleh negatif.',
                            ]),

                        TextInput::make('max_points')
                            ->label('Max Poin')
                            ->required()
                            ->integer()
                            ->minValue(1)
                            ->numeric()
                            ->rules(fn (Get $get, ?TierMember $record): array => [
                                'required',
                                'integer',
                                'min:' . ((int) ($get('min_points') ?? 0) + 1),
                                TierSupport::noOverlapRule($record, (int) ($get('min_points') ?? 0)),
                            ])
                            ->validationMessages([
                                'required' => 'Max poin wajib diisi.',
                                'integer'  => 'Max poin harus berupa bilangan bulat.',
                                'min'      => 'Max poin harus lebih besar dari min poin.',
                            ]),
                    ]),

                Section::make('Konversi Poin')
                    ->description('Nominal transaksi (Rp) yang dibutuhkan untuk mendapatkan 1 poin')
                    ->columns(2)
                    ->schema(function (): array {
                        return TransactionType::orderBy('id')->get()
                            ->map(function (TransactionType $type): TextInput {
                                return TextInput::make(TierMemberFormSupport::conversionFieldKey($type->type_key))
                                    ->label($type->display_name)
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->prefix('Rp')
                                    ->validationMessages([
                                        'required' => "Konversi {$type->display_name} wajib diisi.",
                                        'min'      => "Konversi {$type->display_name} harus lebih dari 0.",
                                    ]);
                            })
                            ->all();
                    }),
            ]);
    }
}
