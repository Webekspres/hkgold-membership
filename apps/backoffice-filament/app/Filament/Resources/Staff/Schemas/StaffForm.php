<?php

declare(strict_types=1);

namespace App\Filament\Resources\Staff\Schemas;

use App\Enums\Role;
use App\Filament\Resources\Staff\Support\StaffFormSupport;
use App\Models\Branch;
use App\Models\Staff;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StaffForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Akun')
                    ->description('Kredensial login staff')
                    ->columnSpanFull()
                    ->columns(6)
                    ->collapsible()
                    ->schema([
                        TextInput::make('full_name')
                            ->label('Nama lengkap')
                            ->required()
                            ->maxLength(150)
                            ->dehydrated(false)
                            ->columnSpan(3),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->rules(fn (?Staff $record): array => [
                                Rule::unique('users', 'email')->ignore($record?->user_id),
                            ])
                            ->dehydrated(false)
                            ->columnSpan(3),
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
                        Select::make('role')
                            ->label('Role')
                            ->options(self::roleOptions())
                            ->default(Role::StoreManager->value)
                            ->required()
                            ->native(false)
                            ->dehydrated(false)
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
                Section::make('Data Staff')
                    ->columnSpanFull()
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        Select::make('branch_id')
                            ->label('Cabang')
                            ->options(fn (): array => Branch::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),
                        TextInput::make('employee_code')
                            ->label('Kode karyawan')
                            ->required()
                            ->maxLength(20)
                            ->default(fn (): string => StaffFormSupport::generateEmployeeCode())
                            ->rules(fn (?Staff $record): array => [
                                Rule::unique('staffs', 'employee_code')->ignore($record?->id),
                            ]),
                    ]),
            ]);
    }

    /**
     * @return array<string, string>
     */
    public static function roleOptions(): array
    {
        return [
            Role::Administrator->value => 'Administrator',
            Role::SuperAdmin->value => 'Super Admin',
            Role::Marketing->value => 'Marketing',
            Role::StoreManager->value => 'Store Manager',
        ];
    }
}
