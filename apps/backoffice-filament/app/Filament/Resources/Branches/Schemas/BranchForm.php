<?php

declare(strict_types=1);

namespace App\Filament\Resources\Branches\Schemas;

use App\Filament\Resources\Branches\Support\BranchFormSupport;
use App\Models\Branch;
use App\Models\City;
use App\Models\PostalCode;
use App\Models\Province;
use App\Models\SubDistrict;
use App\Models\Village;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Data Cabang')
                    ->columnSpanFull()
                    ->columns(5)
                    ->schema([
                        TextInput::make('branch_code')
                            ->label('Kode cabang')
                            ->required()
                            ->maxLength(10)
                            ->default(fn (): string => BranchFormSupport::generateBranchCode())
                            ->disabledOn('edit')
                            ->dehydrated()
                            ->rules(fn (?Branch $record): array => [
                                Rule::unique('branches', 'branch_code')->ignore($record?->id),
                            ])
                            ->columnSpan(2),
                        TextInput::make('phone')
                            ->label('Telepon')
                            ->tel()
                            ->prefix('+62')
                            ->placeholder('81234567890')
                            // ->helperText('Masukkan nomor tanpa awalan 0.')
                            ->maxLength(15)
                            ->columnSpan(2),
                        Toggle::make('is_online_warehouse')
                            ->label('Gudang online')
                            ->default(false)
                            ->live()
                            ->inline(false)
                            ->columnSpan(1),
                        TextInput::make('name')
                            ->label('Nama cabang')
                            ->required()
                            ->maxLength(100)
                            ->columnSpanFull(),
                    ]),
                Section::make('Alamat')
                    ->columnSpanFull()
                    ->columns(3)
                    ->collapsible()
                    ->schema([
                        TextInput::make('location_url')
                            ->label('Link Google Maps')
                            ->url()
                            ->maxLength(500)
                            ->placeholder('https://maps.google.com/...')
                            ->required(fn (Get $get): bool => ! (bool) ($get('is_online_warehouse') ?? false))
                            ->columnSpanFull(),
                        Select::make('province_id')
                            ->label('Provinsi')
                            ->options(fn (): array => Province::query()->orderBy('nama')->pluck('nama', 'id')->all())
                            ->searchable()
                            ->live()
                            ->dehydrated(false)
                            ->afterStateUpdated(function (Set $set): void {
                                $set('city_id', null);
                                $set('sub_district_id', null);
                                $set('village_id', null);
                                $set('postal_code_id', null);
                            }),
                        Select::make('city_id')
                            ->label('Kota/Kabupaten')
                            ->options(fn (Get $get): array => City::query()
                                ->when(
                                    filled($get('province_id')),
                                    fn ($query) => $query->where('province_id', $get('province_id')),
                                    fn ($query) => $query->whereRaw('1 = 0'),
                                )
                                ->orderBy('nama')
                                ->pluck('nama', 'id')
                                ->all())
                            ->searchable()
                            ->live()
                            ->dehydrated(false)
                            ->required(fn (Get $get): bool => filled($get('province_id')))
                            ->disabled(fn (Get $get): bool => blank($get('province_id')))
                            ->afterStateUpdated(function (Set $set): void {
                                $set('sub_district_id', null);
                                $set('village_id', null);
                                $set('postal_code_id', null);
                            }),
                        Select::make('sub_district_id')
                            ->label('Kecamatan')
                            ->options(fn (Get $get): array => SubDistrict::query()
                                ->when(
                                    filled($get('city_id')),
                                    fn ($query) => $query->where('city_id', $get('city_id')),
                                    fn ($query) => $query->whereRaw('1 = 0'),
                                )
                                ->orderBy('nama')
                                ->pluck('nama', 'id')
                                ->all())
                            ->searchable()
                            ->live()
                            ->dehydrated(false)
                            ->required(fn (Get $get): bool => filled($get('city_id')))
                            ->disabled(fn (Get $get): bool => blank($get('city_id')))
                            ->afterStateUpdated(function (Set $set): void {
                                $set('village_id', null);
                                $set('postal_code_id', null);
                            }),
                        Select::make('village_id')
                            ->label('Kelurahan')
                            ->options(fn (Get $get): array => Village::query()
                                ->when(
                                    filled($get('sub_district_id')),
                                    fn ($query) => $query->where('sub_district_id', $get('sub_district_id')),
                                    fn ($query) => $query->whereRaw('1 = 0'),
                                )
                                ->orderBy('nama')
                                ->pluck('nama', 'id')
                                ->all())
                            ->searchable()
                            ->live()
                            ->dehydrated(false)
                            ->required(fn (Get $get): bool => filled($get('sub_district_id')))
                            ->disabled(fn (Get $get): bool => blank($get('sub_district_id'))),
                        Select::make('postal_code_id')
                            ->label('Kode pos')
                            ->options(fn (Get $get): array => PostalCode::query()
                                ->when(
                                    filled($get('sub_district_id')),
                                    fn ($query) => $query->where('sub_district_id', $get('sub_district_id')),
                                    fn ($query) => $query->when(
                                        filled($get('city_id')),
                                        fn ($query) => $query->where('city_id', $get('city_id')),
                                        fn ($query) => $query->whereRaw('1 = 0'),
                                    ),
                                )
                                ->orderBy('kodepos')
                                ->pluck('kodepos', 'id')
                                ->all())
                            ->searchable()
                            ->dehydrated(false)
                            ->required(fn (Get $get): bool => filled($get('village_id')))
                            ->disabled(fn (Get $get): bool => blank($get('village_id'))),
                        Textarea::make('street')
                            ->label('Alamat jalan')
                            ->rows(3)
                            ->maxLength(65535)
                            ->dehydrated(false)
                            ->required(fn (Get $get): bool => filled($get('province_id')))
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
