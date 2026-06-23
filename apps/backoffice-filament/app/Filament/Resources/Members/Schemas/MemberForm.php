<?php

declare(strict_types=1);

namespace App\Filament\Resources\Members\Schemas;

use App\Enums\TierStatus;
use App\Filament\Resources\Members\Support\MemberFormSupport;
use App\Models\District;
use App\Models\Member;
use App\Models\PostalCode;
use App\Models\Province;
use App\Models\Regency;
use App\Models\User;
use App\Models\Village;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class MemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Akun')
                    ->description('Kredensial login member (role: Customer)')
                    ->columnSpanFull()
                    ->columns(6)
                    ->collapsible()
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255)
                            ->dehydrated(false)
                            ->columnSpan(3),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->rules(fn (?Member $record): array => [
                                Rule::unique('users', 'email')->ignore($record?->id),
                            ])
                            ->dehydrated(false)
                            ->columnSpan(3),
                        TextInput::make('phone')
                            ->label('Telepon')
                            ->tel()
                            ->prefix('+62')
                            ->placeholder('81234567890')
                            ->helperText('Masukkan nomor tanpa awalan 0.')
                            ->required()
                            ->maxLength(15)
                            ->rules(fn (?Member $record): array => [
                                function (string $attribute, mixed $value, \Closure $fail) use ($record): void {
                                    $normalized = MemberFormSupport::normalizePhone(is_string($value) ? $value : null);

                                    if (User::query()
                                        ->where('phone', $normalized)
                                        ->when(
                                            $record?->id,
                                            fn ($query, string $id) => $query->where('id', '!=', $id),
                                        )
                                        ->exists()) {
                                        $fail('Nomor telepon sudah terdaftar.');
                                    }
                                },
                            ])
                            ->dehydrated(false)
                            ->columnSpan(2),
                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->rule(Password::default())
                            ->confirmed()
                            ->dehydrated(false)
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->columnSpan(2),
                        TextInput::make('password_confirmation')
                            ->label('Konfirmasi password')
                            ->password()
                            ->revealable()
                            ->dehydrated(false)
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->columnSpan(2),
                        FileUpload::make('profile_photo')
                            ->label('Foto profil')
                            ->avatar()
                            ->imageEditor()
                            ->imageEditorAspectRatios(['1:1'])
                            ->imageAspectRatio('1:1')
                            ->automaticallyCropImagesToAspectRatio()
                            ->automaticallyResizeImagesToWidth('720')
                            ->automaticallyResizeImagesToHeight('720')
                            ->automaticallyUpscaleImagesWhenResizing(false)
                            ->disk('public')
                            ->directory('profile-photos')
                            ->visibility('public')
                            ->maxSize(5_120)
                            ->dehydrated(false)
                            ->columnSpan(6),
                        Toggle::make('is_active')
                            ->label('Akun aktif')
                            ->default(true)
                            ->dehydrated(false)
                            ->columnSpan(2),
                    ]),
                Section::make('Data Member')
                    ->columnSpanFull()
                    ->columns(3)
                    ->collapsible()
                    ->schema([
                        Select::make('tier')
                            ->label('Tier')
                            ->options(self::tierOptions())
                            ->default(TierStatus::Silver)
                            ->required()
                            ->native(false),
                        DatePicker::make('dob')
                            ->label('Tanggal lahir')
                            ->native(false),
                        TextInput::make('total_points')
                            ->label('Total poin')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->step(0.01),
                        Toggle::make('phone_change_pending')
                            ->label('Perubahan telepon tertunda')
                            ->default(false)
                            ->columnSpanFull(),
                    ]),
                Section::make('Alamat')
                    ->columnSpanFull()
                    ->columns(3)
                    ->collapsible()
                    ->schema([
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
                            ->options(fn (Get $get): array => Regency::query()
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
                            ->options(fn (Get $get): array => District::query()
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
                                    filled($get('city_id')),
                                    fn ($query) => $query->where('city_id', $get('city_id')),
                                )
                                ->when(
                                    filled($get('sub_district_id')),
                                    fn ($query) => $query->where('sub_district_id', $get('sub_district_id')),
                                )
                                ->when(
                                    blank($get('city_id')) && blank($get('sub_district_id')),
                                    fn ($query) => $query->whereRaw('1 = 0'),
                                )
                                ->orderBy('kodepos')
                                ->pluck('kodepos', 'id')
                                ->all())
                            ->searchable()
                            ->dehydrated(false)
                            ->required(fn (Get $get): bool => filled($get('village_id')))
                            ->disabled(fn (Get $get): bool => blank($get('city_id')) || blank($get('sub_district_id'))),
                        Textarea::make('street')
                            ->label('Alamat lengkap')
                            ->rows(3)
                            ->maxLength(65535)
                            ->dehydrated(false)
                            ->required(fn (Get $get): bool => filled($get('province_id')))
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * @return array<string, string>
     */
    protected static function tierOptions(): array
    {
        return [
            TierStatus::Silver->value => 'Silver',
            TierStatus::Gold->value => 'Gold',
            TierStatus::Platinum->value => 'Platinum',
            TierStatus::Sapphire->value => 'Sapphire',
        ];
    }
}
