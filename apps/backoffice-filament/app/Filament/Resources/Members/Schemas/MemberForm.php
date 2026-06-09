<?php

declare(strict_types=1);

namespace App\Filament\Resources\Members\Schemas;

use App\Enums\TierStatus;
use App\Models\Address;
use App\Models\Media;
use App\Models\Member;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class MemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Akun')
                    ->description('Kredensial login member (role: Customer)')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255)
                            ->dehydrated(false)
                            ->columnSpanFull(),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->rules(fn (?Member $record): array => [
                                Rule::unique('users', 'email')->ignore($record?->id),
                            ])
                            ->dehydrated(false),
                        TextInput::make('phone')
                            ->label('Telepon')
                            ->tel()
                            ->required()
                            ->maxLength(50)
                            ->rules(fn (?Member $record): array => [
                                Rule::unique('users', 'phone')->ignore($record?->id),
                            ])
                            ->dehydrated(false),
                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->rule(Password::default())
                            ->dehydrated(false)
                            ->required(fn (string $operation): bool => $operation === 'create'),
                        Select::make('profile_photo_id')
                            ->label('Foto profil')
                            ->searchable()
                            ->preload()
                            ->options(fn (?string $state): array => Media::query()
                                ->when(
                                    filled($state),
                                    fn ($query) => $query->where(function ($query) use ($state): void {
                                        $query->whereDoesntHave('user')
                                            ->orWhere('id', $state);
                                    }),
                                    fn ($query) => $query->whereDoesntHave('user'),
                                )
                                ->orderBy('file_name')
                                ->pluck('file_name', 'id')
                                ->all())
                            ->dehydrated(false),
                        Toggle::make('is_active')
                            ->label('Akun aktif')
                            ->default(true)
                            ->dehydrated(false),
                    ]),
                Section::make('Data Member')
                    ->columns(2)
                    ->schema([
                        TextInput::make('member_code')
                            ->label('Kode member')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),
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
                        Select::make('address_id')
                            ->label('Alamat')
                            ->searchable()
                            ->preload()
                            ->options(fn (): array => Address::query()
                                ->orderBy('street')
                                ->get()
                                ->mapWithKeys(fn (Address $address): array => [
                                    $address->id => $address->street ?: "Alamat #{$address->id}",
                                ])
                                ->all()),
                        Toggle::make('phone_change_pending')
                            ->label('Perubahan telepon tertunda')
                            ->default(false),
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
